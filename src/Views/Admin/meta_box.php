<?php
/**
 *
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

/**
 * @var $entity \Simettric\WPSimpleORM\AbstractEntity;
 */
$entity = $this->entityInstance;

foreach($entity->getConfiguredRelations() as $entityName=>$relType)
{ ?>

    <h4><?php echo $entityName ?></h4>

    <p>
        <a href="/wp-admin/index.php?page=<?php echo $entity->getPostType() ?>-relations&post=<?php echo $entity->getPost()->ID ?>&rel=<?php echo str_replace('\\',':', $entityName) ?>"
           target="_blank"><?php _e("View relationships") ?></a>
    </p>


<?php }