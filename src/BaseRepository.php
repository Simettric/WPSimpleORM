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

        $meta_key = $this->getMetaKey($entity->getMetaPrefix(), get_class($entity), true);
        $builder  = $this->createQueryBuilder()
            ->addPostType(call_user_func(array($entityRelatedTo, 'getEntityPostType')))
            ->addMetaQuery(MetaQuery::create($meta_key, $entity->getPost()->ID))
            ->addOrderBy($orderBy)
            ->setOrderDirection($orderDirection);

        if(!$limit)
            $builder->withAnyLimit();

        //die(var_dump($builder->getWPQuery()->request));

        $posts = $builder->getPosts();

        $items = array();
        foreach ($posts as $post)
        {
            $items[] = new $entityRelatedTo($post);
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


        if($post_id = get_post_meta($item->getPost()->ID, $this->getMetaKey($item->getMetaPrefix(), $entity_name), true))
            return new $entity_name(get_post($post_id));

        return null;
    }


    /**
     * @param AbstractEntity $entity
     * @return $this
     * @throws \Exception
     */
    function addRelatedTo(AbstractEntity $entityRelatedTo, AbstractEntity $entity)
    {
        $entity_name = get_class($entityRelatedTo);
        $relations = $entity->getConfiguredRelations();
        if(!isset($relations[$entity_name]))
        {
            throw new \Exception('Relation with "'.$entity_name.'" is not configured');
        }

        if($relations[$entity_name]==WordPressEntityInterface::RELATION_SINGLE)
        {
            if($entity->getPost()){
                update_post_meta($entity->getPost()->ID, $this->getMetaKey($entity->getMetaPrefix(), $entity_name), $entityRelatedTo->getPost()->ID);
            }

        }

        if($relations[$entity_name]==WordPressEntityInterface::RELATION_MULTIPLE)
        {
            add_post_meta($entity->getPost()->ID, $this->getMetaKey($entity->getMetaPrefix(), $entity_name), $entityRelatedTo->getPost()->ID);
            update_post_meta($entityRelatedTo->getPost()->ID,  $this->getMetaKey($entity->getMetaPrefix(), get_class($entity),true), $entity->getPost()->ID);
        }

        return $this;

    }

    function removeRelatedTo(AbstractEntity $entityRelatedTo, AbstractEntity $entity)
    {
        $entity_name = get_class($entityRelatedTo);
        $relations = $entity->getConfiguredRelations();
        if(!isset($relations[$entity_name]))
        {
            throw new \Exception('Relation with "'.$entity_name.'" is not configured');
        }

        if($relations[$entity_name]==WordPressEntityInterface::RELATION_SINGLE)
        {
            delete_post_meta($entity->getPost()->ID, $this->getMetaKey($entity->getMetaPrefix(), $entity_name));
        }

        if($relations[$entity_name]==WordPressEntityInterface::RELATION_MULTIPLE)
        {
            delete_post_meta($entity->getPost()->ID, $this->getMetaKey($entity->getMetaPrefix(), $entity_name), $entityRelatedTo->getPost()->ID);
            delete_post_meta($entityRelatedTo->getPost()->ID, $this->getMetaKey($entity->getMetaPrefix(), get_class($entity), true), $entity->getPost()->ID);
        }

    }


    protected function getMetaKey($prefix, $entityName, $inv=false)
    {
        return $prefix . ($inv ? "_inv_" : "_") . str_replace('\\', '', $entityName);
    }



    /**
     * @return Builder
     */
    protected function createQueryBuilder()
    {
        return new Builder();
    }



}
