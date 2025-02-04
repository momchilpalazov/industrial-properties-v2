<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;
use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function create(object $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function update(object $entity): void
    {
        $this->_em->flush();
    }

    public function delete(object $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function saveMessage(array $data): Contact
    {
        $contact = new Contact();
        $contact->setName($data['name']);
        $contact->setEmail($data['email']);
        $contact->setPhone($data['phone'] ?? null);
        $contact->setSubject($data['subject']);
        $contact->setMessage($data['message']);
        $contact->setIpAddress($data['ip_address'] ?? null);
        $contact->setStatus('new');

        $this->create($contact);

        return $contact;
    }

    public function getUnreadMessagesCount(): int
    {
        return $this->count(['status' => 'new']);
    }

    public function markAsRead(int $id): bool
    {
        $contact = $this->find($id);
        if (!$contact) {
            return false;
        }

        $contact->setStatus('read');
        $this->update($contact);
        return true;
    }

    public function getLatestMessages(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchMessages(array $criteria): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($criteria['status'])) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        if (!empty($criteria['email'])) {
            $qb->andWhere('c.email LIKE :email')
               ->setParameter('email', '%' . $criteria['email'] . '%');
        }

        if (!empty($criteria['date_from'])) {
            $qb->andWhere('c.createdAt >= :date_from')
               ->setParameter('date_from', $criteria['date_from']);
        }

        if (!empty($criteria['date_to'])) {
            $qb->andWhere('c.createdAt <= :date_to')
               ->setParameter('date_to', $criteria['date_to']);
        }

        $qb->orderBy('c.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function deleteOldMessages(int $daysOld = 90): int
    {
        $date = new \DateTime("-{$daysOld} days");
        
        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.createdAt < :date')
            ->setParameter('date', $date->format('Y-m-d H:i:s'))
            ->getQuery()
            ->execute();
    }
} 