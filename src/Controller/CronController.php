<?php

namespace App\Controller;

use App\DTO\ProductCharacteristicsData;
use App\DTO\StockData;
use App\Entity\Category;
use App\Entity\Filter;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\Seo;
use App\Helpers\FileSaver;
use App\Helpers\Translit;
use App\Repository\CartRepository;
use App\Repository\CategoryRepository;
use App\Repository\FilterRepository;
use App\Repository\ImageRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductVariantRepository;
use App\Service\MailSender;
use App\VO\PaymentStatus;
use App\VO\ShipmentStatus;
use Doctrine\ORM\EntityManagerInterface;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Longman\TelegramBot\Telegram;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;
use App\Helpers\SimpleImage;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("cron/v1")
 */
class CronController extends AbstractController
{
    /**
     * @var string
     */
    private $publicDir;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ProductVariantRepository
     */
    private $productVariantRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var MailSender
     */
    private $mailSender;

    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var CartRepository
     */
    private $cartRepository;

    /**
     * @var FilterRepository
     */
    private $filterRepository;

    /**
     * @var ImageRepository
     */
    private $imageRepository;

    /**
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param ProductVariantRepository $productVariantRepository
     * @param EntityManagerInterface $entityManager
     * @param string $publicDir
     * @param RouterInterface $router
     * @param MailSender $mailSender
     * @param PaymentRepository $paymentRepository
     * @param CartRepository $cartRepository
     * @param FilterRepository $filterRepository
     * @param ImageRepository $imageRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ProductVariantRepository $productVariantRepository,
        EntityManagerInterface $entityManager,
        string $publicDir,
        RouterInterface $router,
        MailSender $mailSender,
        PaymentRepository $paymentRepository,
        CartRepository $cartRepository,
        FilterRepository $filterRepository,
        ImageRepository $imageRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->em = $entityManager;
        $this->publicDir = $publicDir;
        $this->router = $router;
        $this->mailSender = $mailSender;
        $this->paymentRepository = $paymentRepository;
        $this->cartRepository = $cartRepository;
        $this->filterRepository = $filterRepository;
        $this->imageRepository = $imageRepository;
    }
//
//    /**
//     * @Route("/remove-carts")
//     */
//    public function deleteOrders()
//    {
//        $carts = $this->cartRepository->findAll();
//
//        foreach ($carts as $cart) {
//            $this->em->remove($cart);
//        }
//
//        $this->em->flush();
//    }

    /**
      * @Route("/filter")
     */
    public function filter()
    {
        set_time_limit(3600);
        ini_set('max_execution_time', 3600);
        ini_set('mysql.connect_timeout', 3600);

        $categories = $this->categoryRepository->findAll();

        foreach ($categories as $category)
        {
//            if ($category->getFilters()->count() > 1) {
//                continue;
//            }

            $products = $category->getProducts();

            /**
             * @var $products Product[]
             */

            $filterFields = [];
            $filters = [];
            foreach ($products as $product) {
                $characteristicsData = $product->getProductCharacteristicsData();
                $variants = $product->getProductVariants();

                foreach ($variants as $variant) {
                    if (!empty($variant->getColor())) {
                        $filterFields['color'] = true;
                    }

                    if (!empty($variant->getSize())) {
                        $filterFields['size'] = true;
                    }
                }

                if (!empty($characteristicsData->getBatteries())) {
                    $filterFields['batteries'] = true;
                }

                if (!empty($characteristicsData->getMaterial())) {
                    $filterFields['material'] = true;
                }

                if (!empty($characteristicsData->getDiameter())) {
                    $filterFields['diameter'] = true;
                }

                if (!empty($characteristicsData->getLenght())) {
                    $filterFields['lenght'] = true;
                }

                foreach ($filterFields as $title => $field) {
                    if (empty($filter = $this->filterRepository->findOneBy(['title' => $title]))) {
                        $filter = new Filter($title);
                        $this->em->persist($filter);
                    }

                    $filters[] = $filter;
                }
            }

            $category->setFilters($filters);

            $this->em->flush();
        }
    }

    /**
     * @Route("/test")
     * @throws \Swift_IoException
     */
    public function test()
    {
        $routes = [];

        foreach ($this->router->getRouteCollection() as $route => $params) {

            if (!preg_match('~^_(.*)~', $route)) {
                $routes[$route] = $route;
            }
        }

        return new JsonResponse(['data' => $routes]);
    }

    /**
     * переводит платеж в статус отменено если платеж не был оплачен в течении 4 часов
     *
     * @Route("/cancel-payments", methods={"GET"})
     *
     * @throws \Exception
     */
    public function cancelPayments()
    {
        $payments = $this->paymentRepository->findBy([
            'status.value' => PaymentStatus::PENDING,
        ]);

        foreach ($payments as $payment) {
            $dateInerval = $payment->getCreatedAt()->diff(new DateTimeImmutable());

            if ($dateInerval->h >= 3) {

                $payment->updateStatus(new PaymentStatus(PaymentStatus::CANCELLED))
                    ->updateCancelDate()
                    ->getOrder()
                    ->getShipment()
                    ->updateStatus(new ShipmentStatus(ShipmentStatus::CANCELLED));
            }

            $this->em->flush();
        }

        return new JsonResponse('ok');
    }

    /**
     * @Route("/import-product", methods={"GET"})
     *
     * @throws \Exception
     */
    public function importProduct()
    {
        set_time_limit(-1);
        ini_set('mysql.connect_timeout', -1);
        ini_set('max_execution_time', -1);

//        foreach ((simplexml_load_file('http://erotikashop.ru/test.xml'))->children() as $element) {
        foreach ((simplexml_load_file('http://stripmag.ru/datafeed/p5s_full.xml'))->children() as $element) {
            $artikul = (string)$element["prodID"] ?? null;
            $title = (string)$element["name"] ?? null;
            $description = (string)$element->description ?? null;
            $diameter = (string)$element["diameter"] ?? null;
            $lenght = (string)$element["lenght"] ?? null;
            $vendor = (string)$element["vendor"] ?? null;
            $material = (string)$element["material"] ?? null;
            $pack = (string)$element["pack"] ?? null;
            $weight = (string)$element['brutto'] ?? null;
            $batteries = (string)$element["batteries"] ?? null;
            $vendorCode = (string)$element["vendorCode"] ?? null;
            $collectionName = (string)$element["CollectionName"] ?? null;
            $categoryTitle = (string)$element->categories->category["Name"] ?? null;
            $subCategoryTitle = (string)$element->categories->category["subName"] ?? null;
            $retailPrice = (float)$element->price['RetailPrice'] ?? null;
            $baseRetailPrice = (float)$element->price['BaseRetailPrice'] ?? null;
            $wholePrice = (float)$element->price['WholePrice'] ?? null;
            $baseWholePrice = (float)$element->price['BaseWholePrice'] ?? null;
            $discount = (string)$element->price['Discount'] ?? null;
            $stopPromo = (string)$element->price['StopPromo'] ?? null;
            $pictures = (array)$element->pictures->picture ?? null;
            $assortiment = (array)$element->assortiment ?? null;

            $productVariants = [];

            if (!empty($product = $this->productRepository->findOneBy(['prodId' => $artikul]))) {
                continue;
            }

            if ($assortiment['assort'] instanceof \SimpleXMLElement) {
                $assort = $assortiment['assort'];

                $color = (string)$assort["color"] ?? null;
                $size = (string)$assort["size"] ?? null;
                $quantityInStock = (string)$assort["sklad"] ?? null;
                $aID = (string)$assort["aID"] ?? null;
                $barcode = (string)$assort["barcode"] ?? null;
                $shippingDate = new DateTimeImmutable((string)$element->assortiment->assort["ShippingDate"]) ?? null;

                if (!empty($this->productVariantRepository->findByBarcode($barcode))) {
                    continue;
                }

                $productVariants[] = new ProductVariant(
                    $aID,
                    $barcode,
                    $color,
                    empty($size) ? null : $size,
                    new StockData(
                        $quantityInStock,
                        $shippingDate,
                        $retailPrice,
                        $wholePrice,
                        $baseWholePrice,
                        $baseRetailPrice,
                        $discount,
                        'RU',
                        'RU'
                    )
                );
            } else {
                foreach ($assortiment['assort'] as $assort) {
                    $color = (string)$assort["color"] ?? null;
                    $size = (string)$assort["size"] ?? null;
                    $quantityInStock = (string)$assort["sklad"] ?? null;
                    $aID = (string)$assort["aID"] ?? null;
                    $barcode = (string)$assort["barcode"] ?? null;
                    $shippingDate = new DateTimeImmutable((string)$element->assortiment->assort["ShippingDate"]) ?? null;

                    if (!empty($this->productVariantRepository->findByBarcode($barcode))) {
                        continue;
                    }

                    $productVariants[] = new ProductVariant(
                        $aID,
                        $barcode,
                        $color,
                        empty($size) ? null : $size,
                        new StockData(
                            $quantityInStock,
                            $shippingDate,
                            $retailPrice,
                            $wholePrice,
                            $baseWholePrice,
                            $baseRetailPrice,
                            $discount,
                            'RU',
                            'RU'
                        )
                    );
                }
            }

            if (empty($category = $this->categoryRepository->findOneBy(['title' => $categoryTitle]))) {
                $category = new Category(
                    $categoryTitle,
                    new Seo(
                        $categoryTitle,
                        Translit::translit($categoryTitle)
                    ),
                    0,
                    ''
                );
                $this->em->persist($category);
                $this->em->flush();
            }

            if (empty($subCategory = $this->categoryRepository->findOneBy(['title' => $subCategoryTitle]))) {
                $subCategory = new Category(
                    $subCategoryTitle,
                    new Seo(
                        $subCategoryTitle,
                        Translit::translit($subCategoryTitle)
                    ),
                    0,
                    ''
                );

                $subCategory->setParent($category);

                $this->em->persist($subCategory);
                $this->em->flush();
            }

            $relativeDir = "/content/products/$artikul";
            $thumbPath = "$relativeDir/thumbnail.jpg";

            $images = [];

            foreach ($pictures as $key => $picture) {
                FileSaver::saveFile("{$this->publicDir}$relativeDir", $picture, "$artikul-$key.jpg");

                $images[] = new Image("$relativeDir/$artikul-$key.jpg", Image::ORIGINAL);
            }

            try {
                $images[] = $this->createPreview($pictures[0], $thumbPath);
            } catch (\Exception $ex) {
            }


            if (!empty($product = $this->productRepository->findOneBy(['prodId' => $artikul]))) {
                $product->updateProductVariant($productVariants);
            } else {
                $product = new Product(
                    $artikul,
                    $vendorCode,
                    $vendor,
                    $title,
                    $description,
                    $retailPrice,
                    new ProductCharacteristicsData(
                        empty($weight) ? null : $weight,
                        empty($batteries) ? null : $batteries,
                        empty($pack) ? null : $pack,
                        empty($material) ? null : $material,
                        empty($lenght) ? null : $lenght,
                        empty($diameter) ? null : $diameter,
                        empty($collectionName) ? null : $collectionName
                    ),
                    !empty($quantityInStock),
                    0,
                    new Seo($title, Translit::translit($title)),
                    $productVariants,
                    [$category, $subCategory],
                    $images ?? []
                );
            }

            $this->em->persist($product);

            foreach ($productVariants as $productVariant) {
                $productVariant->updateProduct($product);
            }

            $this->em->flush();
        }

        return new JsonResponse('ok');
    }

    /**
     * @Route("/update-stock-data", methods={"GET"})
     * @throws \Exception
     */
    public function updateStockData()
    {
        $url = 'http://stripmag.ru/datafeed/p5s_full_stock.xml';

        foreach ((simplexml_load_file($url))->children() as $element) {
//            $prodId = (string)$element['prodID'];
//            $aId = (string) $element->assortiment->assort['aID'];
            $retailPrice = (float)$element->price['RetailPrice'];
            $baseRetailPrice = (float)$element->price['BaseRetailPrice'];
            $wholePrice = (float)$element->price['WholePrice'];
            $baseWholePrice = (float)$element->price['BaseWholePrice'];
            $discount = (int)$element->price['Discount'];
            $quantityInStock = (int)$element->assortiment->assort['sklad'];
            $barcode = (string)$element->assortiment->assort['barcode'];
            $shipingDate = new DateTimeImmutable((string)$element->assortiment->assort['ShippingDate']);

            if (!empty($productVariant = $this->productVariantRepository->findOneBy(['barcode' => $barcode]))) {
                $productVariant->updateStockData(
                    new StockData(
                        $quantityInStock,
                        $shipingDate,
                        $retailPrice,
                        $wholePrice,
                        $baseWholePrice,
                        $baseRetailPrice,
                        $discount,
                        'RU',
                        'RU'
                    )
                );

                $product = $productVariant->getProduct();

                $product->updateEnStock(!empty($quantityInStock));
            } else {
                echo "1";
            }
        }

        $this->em->flush();

        return new JsonResponse('ok');
    }

    /**
     * @param string $img
     * @param string $thumbPath
     *
     * @return Image
     */
    private function createPreview(string $img, string $thumbPath): Image
    {
        (new SimpleImage())->load($img)
            ->resizeToHeight(288)
            ->save("{$this->publicDir}$thumbPath");

        return new Image(
            $thumbPath,
            Image::THUMBNAIL
        );
    }
}
