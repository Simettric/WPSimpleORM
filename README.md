# WPSimpleORM
Not ready for production use yet


USAGE
=====

### Simple entity

    class Person extends AbstractEntity
    {
    
        function getName()
        {
            $this->getTitle();
        }
    
        function getAddress()
        {
            return $this->get('address');
        }
    
        function setAddress($address)
        {
            $this->set('address', $address);
        }
    
        function getPhone()
        {
            return $this->get('phone');
        }
    
        function setPhone($phone)
        {
            $this->set('phone', $phone);
        }
        
        static public function getEntityPostType()
        {
            return 'person';
        }
    
    }
    
    $post = get_post(1);
    
    $person = new Person($post);
    
    echo $person->getId();
    echo $person->getName();
    echo $person->getAddress();
    
    
### Relationships between entities 

    
        class Person extends AbstractEntity
        {
        
            function getName()
            {
                $this->getTitle();
            }
            
            function getVideos($limit=null)
            {
                return $this->repository->getMultipleRelated($this, Video::class, 'ID', "DESC", $limit);
            }
            
            function addVideo(Video $item)
            {
                return $this->repository->addRelatedTo($item, $this);
            }
            
            function removeVideo(Video $item)
            {
                return $this->repository->removeRelatedTo($item, $this);
            }
            
            static public function getEntityPostType()
            {
                return 'person';
            }
            
            public function configure()
            {
                $this->configureRelation(Video::class, static::RELATION_MULTIPLE);
            }
        
        }
        
        $video  = new Video($video_post);
        $person = new Person($post);
        
        $person->addVideo($video);
        
        foreach($person->getVideos() as $video)
        {
            echo $video->getTitle();
        }
        
        $person->removeVideo($video);
    

### Show relationships the meta box and admin page in wp-admin

        //functions.php or your a Plugin
        
        $relationsMenu = new EntityRelationsHooks(Person::class);
        $relationsMenu->registerHooks();