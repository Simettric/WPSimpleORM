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
        
        public function configure()
        {
            
        }
    
    }
    
    $post = get_post(1);
    
    $person = new Person($post);
    $person->setAddress("13 Rue del Percebe");
    
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
                return $this->repository->getRelatedItems($this, Video::class, 'ID', "DESC", $limit);
            }
            
            function addVideo(Video $item)
            {
                return $this->repository->addRelatedItem($this, $item);
            }
            
            function removeVideo(Video $item)
            {
                return $this->repository->removeRelatedItem(this, $item);
            }
            
            static public function getEntityPostType()
            {
                return 'person';
            }
            
            public function configure()
            {
                $this->configureRelation(Video::class, static::ONE_TO_MANY);
            }
        
        }
        
        class Video extends AbstractEntity
        {
        
            function getName()
            {
                $this->getTitle();
            }
            
            function getPerson()
            {
                return $this->repository->getInversedRelatedItem($this, Video::class);
            }
            
            function setPerson(Person $item)
            {
                return $this->repository->addRelatedItem($this, $item);
            }
            
            function removePerson(Person $item)
            {
                return $this->repository->removeRelatedItem(this, $item);
            }
            
            static public function getEntityPostType()
            {
                return 'video';
            }
            
            public function configure()
            {
                $this->configureInverseRelation(Person::class, static::MANY_TO_ONE);
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
        
        // also, you can operate in the reverse way as well
        
        $video->setPerson($person);
        
        echo $video->getPerson();
        
        $video->removePerson();
    

### Show the relationships meta box and admin page in wp-admin

        //functions.php or in your plugin (outside of any hook)
        
        $relationsMenu = new EntityRelationsHooks(Person::class);
        $relationsMenu->registerHooks();