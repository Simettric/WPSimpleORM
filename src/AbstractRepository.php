<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 14/11/16
 * Time: 17:17
 */

namespace Simettric\WPSimpleORM;


use Simettric\WPQueryBuilder\Builder;

abstract class AbstractRepository
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
     * @return Builder
     */
    protected function createQueryBuilder()
    {
        return new Builder();
    }

}
