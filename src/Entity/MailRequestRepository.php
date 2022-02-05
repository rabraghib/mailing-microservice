<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method MailRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailRequest[]    findAll()
 * @method MailRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailRequestRepository extends EntityRepository
{}