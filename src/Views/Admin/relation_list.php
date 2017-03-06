<div class="wrap">

    <h1><?php echo sprintf(__('%s relationShips with %s'), $relEntityName, get_the_title($post)) ?></h1>
    <a href=" <?php echo get_edit_post_link($post->ID) ?>"><?php _e("Back to post edit") ?></a>



    <?php if(false === in_array($relation_type, array(\Simettric\WPSimpleORM\AbstractEntity::MANY_TO_ONE, \Simettric\WPSimpleORM\AbstractEntity::ONE_TO_ONE))  || !count($items)){ ?>
    <form id="new_rel_form" action="" method="post">

        <h2><?php __("Add new relation") ?></h2>
        <table>
            <tr>
                <th>
                    <input id="new_rel" type="search"  autocomplete="off" value="" placeholder="search entity by title">
                    <input type="hidden" id="sim_orm_new_rel_id" name="sim_orm_new_rel_id" value="">
                </th>
                <td>
                    <input id="submit_rel" type="submit" value="Add related" class="button button-primary" />
                </td>
            </tr>

        </table>


    </form>
    <?php } ?>

    <table class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
            <th scope="col" class="manage-column">
                <?php _e("Entity") ?>
            </th>
            <th scope="col" class="manage-column">
                <?php _e("Entity ID") ?>
            </th>
            <th scope="col" class="manage-column"></th>
        </tr>
        </thead>

        <tbody id="the-list">
        <?php

        /**
         * @var $item \Simettric\WPSimpleORM\AbstractEntity
         */
        foreach($items as $item){ ?>
            <tr class="">
                <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                    <strong>
                        <?php echo $item->getTitle() ?>
                    </strong>

                </td>
                <td><?php echo $item->getId() ?></td>
                <td style="text-align: right">
                    <a href="<?php echo $base_link ?>&sim_orm_remove_rel=<?php echo $item->getId() ?>" class="button">remove</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>

        <tfoot>
        <tr>
            <th scope="col" class="manage-column">
                <?php _e("Entity") ?>
            </th>
            <th scope="col" class="manage-column">
                <?php _e("Entity ID") ?>
            </th>
            <th scope="col" class="manage-column"></th>
        </tr>
        </tfoot>

    </table>



</div><!-- .wrap -->