<?php
// src/Repository/PlaceRepository.php

namespace App\Repository;

use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Place>
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    public function findAllWithFilters(array $filters = [], string $sort = 'created_at', string $order = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('p');
        
        // Add WHERE clauses for filters
        if (!empty($filters['category'])) {
            $qb->andWhere('p.category = :category')
            ->setParameter('category', $filters['category']);
        }
        
        if (!empty($filters['city'])) {
            $qb->andWhere('p.city = :city')
            ->setParameter('city', $filters['city']);
        }
        
        if (!empty($filters['rating'])) {
            $qb->andWhere('p.rating >= :rating')
            ->setParameter('rating', (float) $filters['rating']);
        }

        if (!empty($filters['submitted_by'])) {
            $qb->andWhere('p.submitted_by = :submitted_by')
            ->setParameter('submitted_by', $filters['submitted_by']);
        }
        
        // Add ORDER BY for sorting - with proper default
        $validSortFields = ['name', 'category', 'city', 'rating', 'created_at', 'submitted_by'];
        $validOrders = ['ASC', 'DESC'];
        
        $sortField = in_array($sort, $validSortFields) ? $sort : 'created_at';
        $sortOrder = in_array(strtoupper($order), $validOrders) ? strtoupper($order) : 'DESC';
        
        // If no specific sort is requested, default to newest first
        if ($sort === 'created_at' && $order === 'DESC') {
            $qb->orderBy('p.created_at', 'DESC');
        } else {
            $qb->orderBy('p.' . $sortField, $sortOrder);
        }
        
        return $qb->getQuery()->getResult();
    }

    // GET single place by ID - Doctrine already provides find()
    // We can use $this->find($id) directly
    
    // CREATE new place - Use Doctrine's persist() and flush()
    public function save(Place $place): bool
    {
        try {
            // Set timestamps if not set
            if ($place->getCreatedAt() === null) {
                $place->setCreatedAt(new \DateTime());
            }
            $place->setUpdatedAt(new \DateTime());
            
            // Set default submitted_by if not set
            if ($place->getSubmittedBy() === null) {
                $place->setSubmittedBy('anonymous');
            }
            
            $this->getEntityManager()->persist($place);
            $this->getEntityManager()->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // UPDATE place - Use Doctrine's flush()
    public function update(Place $place): bool
    {
        try {
            $place->setUpdatedAt(new \DateTime());
            $this->getEntityManager()->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // DELETE place
    public function remove(Place $place): bool
    {
        try {
            $this->getEntityManager()->remove($place);
            $this->getEntityManager()->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Get all unique cities (for filtering)
    public function getCities(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p.city')
            ->orderBy('p.city', 'ASC');
            
        $result = $qb->getQuery()->getResult();
        
        // Extract city values from result array
        return array_map(function($item) {
            return $item['city'];
        }, $result);
    }

    // Get all categories (for filtering)
    public function getCategories(): array
    {
        return Place::CATEGORIES;
    }

    // Helper method to find by ID (alias for Doctrine's find)
    public function findById($id): ?Place
    {
        return $this->find($id);
    }
}