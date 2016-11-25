<?php
/**
 *
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM\Admin;


use Simettric\WPSimpleORM\AbstractEntity;
use Simettric\WPSimpleORM\BaseRepository;
use Simettric\WPSimpleORM\Exception\IncorrectPostTypeException;

class EntityRelationAdminMenu
{

    /**
     * @var AbstractEntity
     */
    private $entityInstance;

    function __construct($entityClass)
    {
        $this->entityInstance = new $entityClass;
        if(!$this->entityInstance instanceof AbstractEntity)
            throw new \Exception( get_class().' needs an ' . AbstractEntity::class );
    }


    public function registerSubscribers()
    {
        add_action('admin_menu', array($this, 'onAdminMenu'));
        add_action('admin_init', array($this, 'onAdminInit'));
        add_action( 'admin_enqueue_scripts', array($this, 'registerAssets') );

        add_action( 'wp_ajax_sim_wporm_rel', array($this, 'ajaxSearchRelatedPost') );

    }

    public function onAdminMenu()
    {

        add_submenu_page(
            null,
            sprintf(__("%s relations", 'sim-wporm'), get_class($this->entityInstance)),
            sprintf(__("%s relations", 'sim-wporm'), get_class($this->entityInstance)),
            'manage_options',
            'post-relations',
            array($this, 'view')
        );


    }

    public function view()
    {
        if(isset($_GET["post"]) && isset($_GET["rel"]))
        {
            $post = get_post($_GET["post"]);

            $relEntityName = str_replace(':','\\', urldecode($_GET["rel"]));

            if(class_exists($relEntityName, false))
            {

                try{

                    /**
                     * @var $item AbstractEntity
                     */
                    $this->entityInstance->setPost($post);

                    $repository = new BaseRepository(get_class($this->entityInstance));
                    $items      = $repository->getMultipleRelated($this->entityInstance, $relEntityName);


                }catch (IncorrectPostTypeException $e)
                {
                    wp_die($e->getMessage());
                }

                $base_link = "/wp-admin/index.php?page=post-relations&post=".$post->ID ."&rel=" .$_GET["rel"];

                include __DIR__ . '/../Views/Admin/relation_list.php';

            }else{
                wp_die("Related entity {$relEntityName} does not exist");
            }


            

        }else{
            wp_die('Incorrect parameters');
        }
    }

    public function registerAssets()
    {
        if (is_admin()) {

            $screen = get_current_screen();

            if($screen->id == "dashboard_page_post-relations") {

                if (isset($_GET["post"]) && isset($_GET["rel"])) {
                    $post = get_post($_GET["post"]);

                    $relEntityName = $_GET["rel"];
                    $relEntityName = str_replace(':', '\\', urldecode($relEntityName));

                    if (class_exists($relEntityName, false)) {


                        wp_enqueue_script('jquery');
                        wp_enqueue_script( 'jquery-ui-autocomplete' );
                        wp_enqueue_style( 'jquery-ui-styles','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
                        wp_add_inline_script('jquery-ui-autocomplete','jQuery(function($) { $("#new_rel").autocomplete({source: ajaxurl + "?action=sim_wporm_rel&post='.$post->ID.'&rel='.$_GET["rel"].'", delay: 500, minLength: 3, select: function( event, ui ) { $(this).val(ui.item.label); $("#sim_orm_new_rel_id").val(ui.item.value); return false; } }); $("#submit_rel").click(function(e){e.preventDefault(); if(!$("#sim_orm_new_rel_id").val()){ $("#new_rel").val(""); }else{ $("#new_rel_form").submit(); }  }) });');


                    }
                }
            }
        }
    }

    public function onAdminInit()
    {
        if (isset($_GET["page"]) && $_GET["page"]=="post-relations") {

            if(isset($_POST["sim_orm_new_rel_id"]) || isset($_GET["sim_orm_remove_rel"]))
            {
                if (isset($_GET["post"]) && isset($_GET["rel"])) {

                    $post = get_post($_GET["post"]);

                    $relEntityName = $_GET["rel"];
                    $relEntityName = str_replace(':', '\\', urldecode($relEntityName));

                    if (class_exists($relEntityName, false)) {


                        if(isset($_POST["sim_orm_new_rel_id"]) &&
                            ($rel_post = get_post($_POST["sim_orm_new_rel_id"])))
                        {
                            $rel  = new $relEntityName($rel_post);
                            $this->entityInstance->setPost($post);
                            $this->addRelItem($rel);
                        }



                        if(isset($_GET["sim_orm_remove_rel"]) &&
                            ($rel_post = get_post($_GET["sim_orm_remove_rel"])))
                        {
                            $rel  = new $relEntityName($rel_post);
                            $this->entityInstance->setPost($post);
                            $this->removeRelItem($rel);
                        }



                    }
                }
            }



        }
    }

    public function addRelItem(AbstractEntity $rel)
    {
        $repository = new BaseRepository(get_class($this->entityInstance));
        $repository->addRelatedTo($rel, $this->entityInstance);

        wp_redirect("/wp-admin/index.php?page=post-relations&post=".$this->entityInstance->getPost()->ID ."&rel=" .$_GET["rel"]);
        exit();
    }

    public function removeRelItem(AbstractEntity $rel)
    {
        $repository = new BaseRepository(get_class($this->entityInstance));
        $repository->removeRelatedTo($rel, $this->entityInstance);


        wp_redirect("/wp-admin/index.php?page=post-relations&post=".$this->entityInstance->getPost()->ID ."&rel=" .$_GET["rel"]);
        exit();
    }

    public function ajaxSearchRelatedPost()
    {
        if (isset($_GET["post"]) && isset($_GET["rel"])) {


            $relEntityName = $_GET["rel"];
            $relEntityName = str_replace(':', '\\', urldecode($relEntityName));

            if (class_exists($relEntityName, false)) {

                $post_type = call_user_func(array($relEntityName, 'getEntityPostType'));

                $posts = get_posts(array('post_type'=>$post_type, 's'=> $_GET["term"], 'posts_per_page'=>-1));

                $result = array();
                foreach ($posts as $post)
                {
                    $result[] = array(
                        "label" => get_the_title($post),
                        "value" => $post->ID
                    );
                }

                wp_send_json($result);
            }
        }
    }

}