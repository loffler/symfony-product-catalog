<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('products', 'products_')]
class ProductController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        $products = $doctrine
            ->getRepository(Product::class)
            ->findAll();

        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
            ];
        }


        return $this->json($data);
    }

    #[Route('/{id}', name: 'by_id', methods: ['GET'])]
    public function getById(int $id): Response
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);
        if ($product) {
            return $this->json([
                'id' => $product->getId(),
                'name' => $product->getName(),
            ]);
        } else {
            return $this->json(['error' => 'Product not found']);
        }
    }

    #[Route('', name: 'new', methods: ['POST'])]
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);

        $product = new Product();
        $product->setName($data['name']);
        $product->setProductCategory($data['category']);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json('Created new product successfully with id ' . $product->getId());
    }
}
