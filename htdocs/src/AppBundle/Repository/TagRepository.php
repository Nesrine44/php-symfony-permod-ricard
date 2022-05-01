<?php

namespace AppBundle\Repository;
use AppBundle\Entity\Settings;
use AppBundle\Entity\Tag;

/**
 * TagRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TagRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * Get or create Tag.
     *
     * @param $title
     * @return Tag|bool|object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getOrCreateTag($title){
        if(!$title){
            return false;
        }
        $em = $this->getEntityManager();
        if(is_numeric($title)){
            $tag = $this->findOneBy(['id' => $title]);
        }else{
            $tag = $this->findOneBy(['title' => $title]);
        }
        if (!$tag) {
            $tag = new Tag();
            $tag->setTitle(Settings::getXssCleanString($title));
            $em->persist($tag);
            $em->flush();
        }
        return $tag;
    }


    /**
     * Search Tag by title.
     *
     * @param $title
     * @param boolean $to_select2_array
     * @param int $offset
     * @param int $limit
     * @return array|mixed
     */
    public function searchTagByTitle($title, $to_select2_array = false, $offset = 0, $limit = 20){
        $qb = $this->_em->createQueryBuilder();
        $qb->select('t')
            ->from('AppBundle:Tag', 't');
        $qb->addSelect("(CASE WHEN t.title like :search_first THEN 2 WHEN t.title like :global_search THEN 1 ELSE 0 END) AS HIDDEN ORD ");
        $qb->where('t.title LIKE :title');
        $qb->setParameter('title', '%' . $title . '%');
        $qb->setParameter('search_first', $title . '%');
        $qb->setParameter('global_search', '%' . $title . '%');
        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);
        $qb->orderBy('ORD', 'DESC');
        $tags = $qb->getQuery()
            ->getResult();
        if($to_select2_array){
            $ret = array();
            foreach ($tags as $tag){
                $ret[] = $tag->toSelect2Array();
            }
            return $ret;
        }
        return $tags;
    }
}