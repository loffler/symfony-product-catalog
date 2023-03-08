<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('products', 'products_')]
class ProductController extends AbstractController
{
    const PRODUCT_CACHE_TTL = 10;
    const PRODUCT_INDEX_CACHE_KEY = 'product_index';
    const PRODUCT_DETAIL_CACHE_KEY = 'product_detail';

    public function __construct(
        private PaginatedFinderInterface $finder,
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private SerializerInterface $serializer,
        private CacheInterface $cache,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        // Various sorting, filtering and pagination could be done in this endpoint.
        // But I would prefer using API Platform if it needed to be done.

        $products = $this->cache->get(self::PRODUCT_INDEX_CACHE_KEY, function (ItemInterface $item) {
            $item->expiresAfter(self::PRODUCT_CACHE_TTL);
            return $this->productRepository
                ->findAll();
        });

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('list_product')
            ->toArray();

        return $this->json($products, context: $context);
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $search = $request->get('fulltext');
        $result = $this->finder->find($search);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('show_product')
            ->toArray();
        return $this->json($result, context: $context);
    }

    #[Route('/{id}', name: 'by_id', methods: ['GET'])]
    public function getById(int $id): Response
    {
        $product = $this->cache->get(
            sprintf('%s_%s', self::PRODUCT_DETAIL_CACHE_KEY, $id),
            function (ItemInterface $item) use ($id) {
                $item->expiresAfter(self::PRODUCT_CACHE_TTL);
                return $this->productRepository->findOrFail($id);
            }
        );

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('show_product')
            ->toArray();

        return $this->json($product, context: $context);
    }

    #[Route('', name: 'new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json', [
            'allow_extra_attributes' => false,
        ]);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('show_product')
            ->toArray();

        return $this->json($product, context: $context);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): Response
    {
        $product = $this->productRepository->findOrFail($id);

        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json', [
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            AbstractNormalizer::OBJECT_TO_POPULATE => $product,
        ]);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('show_product')
            ->toArray();

        return $this->json($product, context: $context);
    }
}
