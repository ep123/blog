<?php
class Admin_PostsController extends Zend_Controller_Action
{
     /**
     * Check if user is logged on 
     */
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_helper->redirector->setGotoRoute(array('controller'=>'auth', 'action'=>'index'),'admin');
        }
    }
    
    /**
     * Index Action 
     */
    public function indexAction()
    {   
        $posts = new Admin_Model_Posts();
        $this->view->posts = $posts->findAll(); 
        $this->view->title = _("Posts");
        $tagsList = new Admin_Model_Tags();
        $this->view->tags = $tagsList->findTags();
  
    }
    
    /**
     * Add post 
     */
    public function addAction()
    {
        $form = new Admin_Form_Posts(); 
        $form->submit->setLabel('Add');
        $this->view->form = $form;     
        if ($this->getRequest()->isPost()) {        
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $title = $form->getValue('title'); 
                $full_text = $form->getValue('full_text');
                if(!$form->getValue('url')){
                    $url = $this->_helper->UrlConverter($form->getValue('title'));
                }
                $is_active = $form->getValue('is_active');
                $tags = $form->getValue('tags');
                $tags = explode(',', $tags);
                $category = $form->getValue('parent_id'); 
                $auth = Zend_Auth::getInstance();  
                $post = new Admin_Model_Posts();
                $post->addPost($title, $full_text,$url, $is_active, $category, $auth->getIdentity()->id, date("y.m.d H:i:s"));  
                $post = new Admin_Model_Tags();
                $post->setTags($tags, $post->getAdapter()->lastInsertId('posts'));
                $this->_helper->redirector->setGotoRoute(array('controller'=>'posts', 'action'=>'index'),'admin');;
            } else {
                $form->populate($formData);
            }
        }
    }   
    /**
     * Edit Post 
     */
    public function editAction()
    {
        $form = new Admin_Form_Posts(); 
        $form->submit->setLabel('Add');
        $this->view->form = $form;       
        $id = $this->_getParam('id');
        if ($id > 0) {
            $post = new Admin_Model_Posts();
            $tagsList = new Admin_Model_Tags();
            $form->tags->setValue($tagsList->getTagsByPost($this->_getParam('id'),1));
            $form->submit->setLabel('Edit');
            $form->populate($post->getPostById($id));
        }
        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $id = (int)$form->getValue('id');
                $title = $form->getValue('title'); 
                $full_text = $form->getValue('full_text');
                $tags = $form->getValue('tags');
                $tags = explode(',', $tags);    
                if(!$form->getValue('url')){
                    $url = $this->_helper->UrlConverter($form->getValue('title'));
                }
                $is_active = $form->getValue('is_active');
                $category = $form->getValue('parent_id');
                $post = new Admin_Model_Posts();
                $post->updatePost($id, $title, $full_text, $url, $is_active, $category, date("y.m.d H:i:s"));                  
                $post = new Admin_Model_Tags();
                $post->delTags($this->_getParam('id'));
                $post->setTags($tags, $this->_getParam('id'));   
                $this->_helper->redirector->setGotoRoute(array('controller'=>'posts', 'action'=>'index'),'admin');
            } else {
                $form->populate($formData);
            }
        }
    }
}

