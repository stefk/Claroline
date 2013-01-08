<?php

namespace Claroline\CoreBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MessageRepository extends NestedTreeRepository
{
    public function getAncestors($message)
    {
        $dql = "SELECT m FROM Claroline\CoreBundle\Entity\Message m
            WHERE m.lft BETWEEN m.lft AND m.rgt
            AND m.root = {$message->getRoot()}
            AND m.lvl <= {$message->getLvl()}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getUnreadMessages($user)
    {
        $dql = "SELECT m FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user u
            WHERE u.id = {$user->getId()}
            AND um.isRead = 0
            AND um.isRemoved = 0"
           ;

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getUserReceivedMessages($user, $isRemoved = false, $offset = null, $limit = null)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemoved}";

        $query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function getSentMessages($user, $isRemoved = false, $offset = null, $limit = null)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT m, u, um, umu FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user umu
            JOIN m.user u
            WHERE u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}";

        $query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchUserReceivedMessages($search, $user, $isRemoved = false, $offset = null, $limit = null)
    {
        $search = strtoupper($search);
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT um, m, u, mu FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            JOIN m.user mu
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemoved}
            AND UPPER(m.object) LIKE :search
            OR UPPER(mu.username) LIKE :search
            AND um.isRemoved = {$isRemoved}
            AND u.id = {$user->getId()}";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchSentMessages($search, $user, $isRemoved = false, $offset = null, $limit = null)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $search = strtoupper($search);
        $dql = "SELECT m, u, um, umu FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user umu
            JOIN m.user u
            WHERE u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}
            AND UPPER (m.object) LIKE :search
            OR UPPER (umu.username) LIKE :search
            AND u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function getRemovedMessages($user, $offset = null, $limit = null)
    {
        $dql = "SELECT um, m, u, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            JOIN m.user mu
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = 1
            OR mu.id = {$user->getId()}
            AND m.isRemoved = 1";

        $query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchRemovedMessages($search, $user, $offset = null, $limit = null)
    {
        $search = strtoupper($search);

        $dql = "SELECT um, m, u, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            JOIN m.user mu
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = 1
            AND UPPER(m.object) LIKE :search
            OR mu.id = {$user->getId()}
            AND m.isRemoved = 1
            AND UPPER(m.object) LIKE :search
            OR m.isRemoved = 1
            AND UPPER(mu.username) LIKE :search
            OR um.isRemoved = 1
            AND u.id = {$user->getId()}
            AND UPPER(mu.username) LIKE :search";

        $query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $query->setParameter('search', "%{$search}%");
        $paginator = new Paginator($query, true);

        return $paginator;
    }
}