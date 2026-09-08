<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Yzqde\DouyinSpider\DTO\ArticleCreateDto;
use Yzqde\DouyinSpider\Model\Article;
use Yzqde\DouyinSpider\Model\User;
use Yzqde\DouyinSpider\Repository\ArticleRepository;

class ArticleService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ArticleRepository $articleRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 分页获取文章列表，预加载作者信息
     *
     * @return array{items: Article[], total: int, page: int, limit: int}
     */
    public function list(int $page = 1, int $limit = 20): array
    {
        $items = $this->articleRepository->findPaginated($page, $limit);
        $total = $this->articleRepository->countAll();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    public function findById(int $id): ?Article
    {
        return $this->articleRepository->find($id);
    }

    public function create(ArticleCreateDto $dto, int $authorId): Article
    {
        $article = new Article();
        $article->setTitle($dto->title);
        $article->setContent($dto->content);
        $article->setSummary($dto->summary);
        // 使用 EntityManager 的 getReference() 创建 User 代理对象，避免反射设置 ID
        $author = $this->em->getReference(User::class, $authorId);
        $article->setAuthor($author);

        $this->em->persist($article);
        $this->em->flush();

        $this->logger->info('文章创建成功', ['articleId' => $article->getId(), 'authorId' => $authorId]);

        return $article;
    }

    public function update(Article $article, ArticleCreateDto $dto): Article
    {
        $article->setTitle($dto->title);
        $article->setContent($dto->content);
        $article->setSummary($dto->summary);

        $this->em->flush();

        $this->logger->info('文章更新成功', ['articleId' => $article->getId()]);

        return $article;
    }

    public function delete(Article $article): void
    {
        $articleId = $article->getId();
        $this->em->remove($article);
        $this->em->flush();

        $this->logger->info('文章删除成功', ['articleId' => $articleId]);
    }

    public function incrementViewCount(Article $article): void
    {
        $article->incrementViewCount();
        $this->em->flush();
    }
}
