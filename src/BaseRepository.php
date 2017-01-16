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
            ->setOffset($offset);

        return $builder->getPosts();
    }

    /**
     * @param AbstractEntity $entity
     * @param $entityRelatedTo
     * @param string $orderBy
     * @param string $orderDirection
     * @param null $limit
     * @param int $offset
     * @return array AbstractEntity[]
     */
    public function getMultipleRelated( AbstractEntity $entity,
                                        $entityRelatedTo,
                                        $orderBy='ID',
                                        $orderDirection="DESC",
                                        $limit=null,
                                        $offset=0 )
    {

        $repository        = $this;
        $relations         = $entity->getConfiguredRelations();
        $inversedRelations = $entity->getConfiguredInversedRelations();

        $entityInstance    = $entity;
        $relatedClass      = $entityRelatedTo;

        if(!isset($relations[$entityRelatedTo]))
        {
            if(isset($inversedRelations[$entityRelatedTo]))
            {
                $repository = new BaseRepository($entityRelatedTo);
                $entityInstance = new $entityRelatedTo;
                $relatedClass   = get_class($entity);
            }else{
                throw new \Exception('Relation with "'.$entityRelatedTo.'" is not configured');
            }
        }

        $meta_key = $repository->getMetaKey($entityInstance->getMetaPrefix(), get_class($entityInstance), true);
        $builder  = $repository->createQueryBuilder()
            ->addPostType(call_user_func(array($relatedClass, 'getEntityPostType')))
            ->addMetaQuery(MetaQuery::create($meta_key, $entityInstance->getPost()->ID))
            ->addOrderBy($orderBy)
            ->setOrderDirection($orderDirection);

        if(!$limit)
            $builder->withAnyLimit();

        $posts = $builder->getPosts();

        $items = array();
        foreach ($posts as $post)
        {
            $items[] = new $relatedClass($post);
        }

        return $items;

    }


    /**
     * @param AbstractEntity $item
     * @param $entity_name
     *
     * @return AbstractEntity|null
     */
    public function getSingleRelated(AbstractEntity $item, $entity_name)
    {

        $relations = $item->getConfiguredRelations();
        if(!isset($relations[$entity_name]))
        {
            throw new \Exception('Relation with "'.$entity_name.'" is not configured');
        }

        $relations         = $item->getConfiguredRelations();
        $inversedRelations = $item->getConfiguredInversedRelations();

        $entityInstance    = $item;
        $relatedClass      = $entity_name;

        if(!isset($relations[$relatedClass]))
        {
            if(isset($inversedRelations[$relatedClass]))
            {
                $entityInstance = new $relatedClass;
                $relatedClass   = get_class($item);
            }else{
                throw new \Exception('Relation with "'.$entity_name.'" is not configured');
            }
        }


        if($post_id = get_post_meta($entityInstance->getPost()->ID, $this->getMetaKey($entityInstance->getMetaPrefix(), $relatedClass), true))
            return new $relatedClass(get_post($post_id));

        return null;
    }


    /**
     * @param AbstractEntity $entity
     * @return $this
     * @throws \Exception
     */
    function addRelatedTo(AbstractEntity $entityRelatedTo, AbstractEntity $entity)
    {


        $repository        = $this;
        $entity_name       = get_class($entityRelatedTo);
        $relations         = $entity->getConfiguredRelations();
        $inversedRelations = $entity->getConfiguredInversedRelations();

        $entityInstance    = $entity;

        if(!isset($relations[$entity_name]))
        {
            if(isset($inversedRelations[$entity_name]))
            {
                $repository      = new BaseRepository($entityRelatedTo);
                $entityInstance  = $entityRelatedTo;
                $entity_name     = get_class($entity);
                $entityRelatedTo = $entity;
                $type            = $inversedRelations[$entity_name];
            }else{
                throw new \Exception('Relation with "'.$entityRelatedTo.'" is not configured');
            }
        }else{
            $type  =  $relations[$entity_name];
        }

        if($type==WordPressEntityInterface::RELATION_SINGLE)
        {
            if($entityInstance->getPost()){
                update_post_meta($entityInstance->getPost()->ID, $repository->getMetaKey($entityInstance->getMetaPrefix(), $entity_name), $entityRelatedTo->getPost()->ID);
            }

        }

        if($type==WordPressEntityInterface::RELATION_MULTIPLE)
        {
            add_post_meta($entityInstance->getPost()->ID, $repository->getMetaKey($entityInstance->getMetaPrefix(), $entity_name), $entityRelatedTo->getPost()->ID);
            update_post_meta($entityRelatedTo->getPost()->ID,  $repository->getMetaKey($entityInstance->getMetaPrefix(), get_class($entity),true), $entityInstance->getPost()->ID);
        }

        return $this;

    }

    function removeRelatedTo(AbstractEntity $entityRelatedTo, AbstractEntity $entity)
    {
        $repository        = $this;
        $entity_name       = get_class($entityRelatedTo);
        $relations         = $entity->getConfiguredRelations();
        $inversedRelations = $entity->getConfiguredInversedRelations();

        $entityInstance    = $entity;

        if(!isset($relations[$entity_name]))
        {
            if(isset($inversedRelations[$entity_name]))
            {
                $repository      = new BaseRepository($entityRelatedTo);
                $entityInstance  = $entityRelatedTo;
                $entity_name     = get_class($entity);
                $entityRelatedTo = $entity;
                $type            = $inversedRelations[$entity_name];
            }else{
                throw new \Exception('Relation with "'.$entityRelatedTo.'" is not configured');
            }
        }else{
            $type  =  $relations[$entity_name];
        }

        if($type==WordPressEntityInterface::RELATION_SINGLE)
        {
            delete_post_meta($entityInstance->getPost()->ID, $repository->getMetaKey($entityInstance->getMetaPrefix(), $entity_name));
        }

        if($type==WordPressEntityInterface::RELATION_MULTIPLE)
        {
            delete_post_meta($entityInstance->getPost()->ID, $repository->getMetaKey($entityInstance->getMetaPrefix(), $entity_name), $entityRelatedTo->getPost()->ID);
            delete_post_meta($entityRelatedTo->getPost()->ID, $repository->getMetaKey($entityInstance->getMetaPrefix(), get_class($entityInstance), true), $entityInstance->getPost()->ID);
        }

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
