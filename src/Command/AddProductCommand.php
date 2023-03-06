<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\ProductCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-product',
    description: 'Command for adding testing product.',
)]
class AddProductCommand extends Command
{
    public function __construct(private EntityManagerInterface $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('product_name', InputArgument::REQUIRED, 'Product name')
            ->addArgument('category_name', InputArgument::REQUIRED, 'Category name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $productName = $input->getArgument('product_name');
        $categoryName = $input->getArgument('category_name');

        if ($productName) {
            $io->note(sprintf('Name: %s', $productName));
        }

        $product = new Product();
        $product->setName($productName);

        $productCategory = new ProductCategory();
        $productCategory->setName($categoryName);

        $product->setProductCategory($productCategory);
        $this->manager->persist($product);
        $this->manager->flush();

        $io->success('Dummy product successfully created');

        return Command::SUCCESS;
    }
}
