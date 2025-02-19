<?php

namespace App\Command;

use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-blog-slugs',
    description: 'Updates slugs for all blog posts',
)]
class UpdateBlogSlugsCommand extends Command
{
    public function __construct(
        private BlogPostRepository $blogPostRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $posts = $this->blogPostRepository->findAll();
        $count = 0;

        foreach ($posts as $post) {
            $post->updateSlug();
            $count++;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Successfully updated slugs for %d blog posts.', $count));

        return Command::SUCCESS;
    }
} 