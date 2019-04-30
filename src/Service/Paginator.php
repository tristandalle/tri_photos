<?php

namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Paginator extends AbstractController{
    private $entityClass;
    private $limit = 10;
    private $currentPage = 1;
    private $manager;
    private $where = [];

    public function setWhere($whereArray)
    {
        $this->where = $whereArray;
    }

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function getData()
    {
        $offset = $this->currentPage * $this->limit - $this->limit;

        $repository = $this->manager->getRepository($this->entityClass);
        $data = $repository->findBy($this->where, [], $this->limit, $offset);

        return $data;
    }

    public function getPages()
    {
        $repository = $this->manager->getRepository($this->entityClass);
        $total = count($repository->findBy($this->where));
        $pages = ceil($total / $this->limit);

        return $pages;
    }

    public function getPagesWithAuthor()
    {
        $repository = $this->manager->getRepository($this->entityClass);
        $currentUser = $this->getUser();
        $total = count($repository->findBy(['author' => $currentUser]));
        $pages = ceil($total / $this->limit);

        return $pages;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     * @return self
     */
    public function setEntityClass($entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return self
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     * @return self
     */
    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;

        return $this;
    }
}