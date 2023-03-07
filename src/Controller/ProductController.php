<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('products', 'products_')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private SerializerInterface $serializer) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('list_product')
            ->toArray();

        return $this->json($products, context: $context);
    }

    #[Route('/{id}', name: 'by_id', methods: ['GET'])]
    public function getById(int $id): Response
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('show_product')
            ->toArray();

        if (!$product) {
            return $this->json(['error' => 'Product not found']);
        }
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
        $product = $this->productRepository->findOneBy(['id' => $id]);
        if (!$product) {
            return $this->json(['error' => 'Product not found']);
        }

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
