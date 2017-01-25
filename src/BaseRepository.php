<?php
/**
 * This is the default repository used to perform queries, you can extend it in
 * in order to add new methods and properties
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM;


use Simettric\WPQueryBuilder\Builder;
use Simettric\WPQueryBuilder\MetaQuery;

class BaseRepository
{

    /**
     * @var string
     */
    private $entity_name;

    public function __construct($entity_name)
    {
        $this->entity_name = $entity_name;
    }

    /**
     * @param $id
     * @return AbstractEntity|null
     */
    public function find($id)
    {
        if($post = get_post($id))
        {
            return new $this->entity_name($post);
        }
        return null;
    }


    /**
     * todo: implement criteria
     * @param array $criteria
     * @param string $orderBy
     * @param string $orderDirection
     * @param int $limit
     * @param int $offset
     * @return array
     *
     */
    public function findBy( $criteria=array(),
                            $orderBy='ID',
                            $orderDirection="DESC",
                            $limit=10,
                            $offset=0 )
    {
        $builder = $this->createQueryBuilder()
            ->addOrderBy($orderBy)
            ->setOrderDirection($orderDirection)
            ->setLimit($limit)
            ->addPostType(call_user_func(array($this->entity_name, 'getEntityPostType')))
            ->setOffset($offset);

        $items = array();
        $posts = $builder->getPosts();
        foreach ($posts as $post)
        {
            $items[] = new $this->entity_name($post);
        }

        return $items;
    }

    public function getRelatedItems( AbstractEntity $entity,
                                  $entityRelatedTo,
                                  $orderBy='ID',
                                  $orderDirection="DESC",
                                  $limit=null,
                                  $offset=0 )
    {

        $builder  = $this->createQueryBuilder()
            ->addPostType(call_user_func(array($entityRelatedTo, 'getEntityPostType')))
            ->addMetaQuery(MetaQuery::create($entity->getRelationMetaKey(), $entity->getPost()->ID))
            ->addOrderBy($orderBy)
            ->setOrderDirection($orderDirection);

        if(!$limit){
            $builder->withAnyLimit();
        }else{
            $builder->setLimit($limit)
                ->setOffset($offset);
        }


        $posts = $builder->getPosts();

        $items = array();
        foreach ($posts as $post)
        {
            $items[] = new $entityRelatedTo($post);
        }

        return $items;
    }

    public function getInversedRelatedItems( AbstractEntity $entity,
                                  $entityRelatedTo,
                                  $orderBy='ID',
                                  $orderDirection="DESC",
                                  $limit=null,
                                  $offset=0 )
    {

        $builder  = $this->createQueryBuilder()
            ->addPostType(call_user_func(array($entityRelatedTo, 'getEntityPostType')))
            ->addMetaQuery(MetaQuery::create($entity->getInverseRelationMetaKey(), $entity->getPost()->ID))
            ->addOrderBy($orderBy)
            ->setOrderDirection($orderDirection);

        if(!$limit){
            $builder->withAnyLimit();
        }else{
            $builder->setLimit($limit)
                ->setOffset($offset);
        }


        $posts = $builder->getPosts();

        $items = array();
        foreach ($posts as $post)
        {
            $items[] = new $entityRelatedTo($post);
        }

        return $items;
    }



    public function getRelatedItem( AbstractEntity $entity, $entityRelatedClass)
    {
        /**
         * @var $item AbstractEntity
         */
        $item = new $entityRelatedClass;
        if($post = get_post_meta($entity->getPost()->ID, $item->getRelationMetaKey(), true))
        {
            $item->setPost($post);
            return $item;
        }

        return null;
    }


    public function getInversedRelatedItem( AbstractEntity $entity, $entityRelatedClass)
    {
        /**
         * @var $item AbstractEntity
         */
        $item = new $entityRelatedClass;
        if($post = get_post_meta($entity->getPost()->ID, $item->getInverseRelationMetaKey(), true))
        {
            $item->setPost($post);
            return $item;
        }

        return null;
    }



    public function addRelatedItem(AbstractEntity $item, AbstractEntity $itemRelated, $relType)
    {

        if($relType == AbstractEntity::ONE_TO_ONE)
        {
            update_post_meta($item->getPost()->ID, $itemRelated->getRelationMetaKey(), $itemRelated->getId());
            update_post_meta($itemRelated->getPost()->ID, $item->getInverseRelationMetaKey(), $item->getId());
            return;
        }

        if($relType == AbstractEntity::ONE_TO_MANY)
        {
            add_post_meta($item->getPost()->ID, $itemRelated->getRelationMetaKey(), $itemRelated->getId());
            update_post_meta($itemRelated->getPost()->ID, $item->getInverseRelationMetaKey(), $item->getId());
            return;
        }

        if($relType == AbstractEntity::MANY_TO_ONE)
        {
            update_post_meta($item->getPost()->ID, $itemRelated->getRelationMetaKey(), $itemRelated->getId());
            add_post_meta($itemRelated->getPost()->ID, $item->getInverseRelationMetaKey(), $item->getId());
            return;
        }

        if($relType == AbstractEntity::MANY_TO_MANY)
        {
            add_post_meta($item->getPost()->ID, $itemRelated->getRelationMetaKey(), $itemRelated->getId());
            add_post_meta($itemRelated->getPost()->ID, $item->getInverseRelationMetaKey(), $item->getId());
            return;
        }

        throw new \Exception("Invalid relation type");

    }


    function removeRelatedItem(AbstractEntity $item, AbstractEntity $itemRelated)
    {

        delete_post_meta($item->getPost()->ID, $itemRelated->getRelationMetaKey(), $itemRelated->getId());
        delete_post_meta($itemRelated->getPost()->ID, $item->getInverseRelationMetaKey(), $item->getId());

    }

    function removeAllRelatedItems(AbstractEntity $item, $itemRelatedClass)
    {

        /**
         * @var $itemRelated AbstractEntity
         */
        $itemRelated = new $itemRelatedClass;

        delete_post_meta($item->getPost()->ID, $itemRelated->getRelationMetaKey());
        delete_post_meta($itemRelated->getPost()->ID, $item->getInverseRelationMetaKey());

    }


    /**
     * @param AbstractEntity $entityRelated
     * @param AbstractEntity $entity
     * @return null
     * @throws \Exception
     */
    function addRelatedTo(AbstractEntity $entityRelated, AbstractEntity $entity)
    {

        $entity_name       = get_class($entityRelated);
        $relations         = $entity->getConfiguredRelations();
        $inversedRelations = $entity->getConfiguredInversedRelations();


        $inversed = false;

        if(!isset($relations[$entity_name]))
        {
            if(isset($inversedRelations[$entity_name]))
            {
                $type     = $inversedRelations[$entity_name];
                $inversed = true;

            }else{
                throw new \Exception('Relation with "'.$entity_name.'" is not configured');
            }
        }else{
            $type = $relations[$entity_name];
        }


        if($inversed)
        {
            $this->addRelatedItem($entityRelated, $entity, $type);

        }else{
            $this->addRelatedItem($entity, $entityRelated, $type);
        }


        return null;

    }

    function removeRelatedTo(AbstractEntity $entityRelated, AbstractEntity $entity)
    {
        $entity_name       = get_class($entityRelated);
        $relations         = $entity->getConfiguredRelations();
        $inversedRelations = $entity->getConfiguredInversedRelations();


        $inversed = false;

        if(!isset($relations[$entity_name]))
        {
            if(isset($inversedRelations[$entity_name]))
            {
                $inversed = true;

            }else{
                throw new \Exception('Relation with "'.$entity_name.'" is not configured');
            }
        }


        if($inversed)
        {
            $this->removeRelatedItem($entityRelated, $entity);

        }else{
            $this->removeRelatedItem($entity, $entityRelated);
        }


        return null;

    }



    protected function getMetaKey($prefix, $entityName, $inv=false)
    {
        return static::getRelationMetaKey($prefix, $entityName, $inv);
    }


    static function getRelationMetaKey($entityPrefix, $relEntityName, $inv=false)
    {
        return $entityPrefix . ($inv ? "_inv_" : "_") . str_replace('\\', '', $relEntityName);
    }


    /**
     * @return Builder
     */
    protected function createQueryBuilder()
    {
        return new Builder();
    }



}
