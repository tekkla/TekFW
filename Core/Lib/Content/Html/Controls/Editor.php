<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\FormElementAbstract;
use Core\Lib\Content\Html\Form\Input;
use Core\Lib\Content\Html\Elements\Div;
use Core\Lib\Content\Html\HtmlFactory;

/**
 * Creates a CKE inline control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Controls
 * @license MIT
 * @copyright 2014 by author
 */
class Editor extends FormElementAbstract
{

    /**
     * Height in px
     *
     * @var int
     */
    private $height = 600;

    /**
     * Background color (hex)
     *
     * @var string
     */
    private $color = '#666';

    /**
     * Use filebrowser flag
     *
     * @var bool
     */
    private $filebrowser_use = true;

    /**
     * Filebrowser width
     *
     * @var int string
     */
    private $filebrowser_width = 600;

    /**
     * Filebrowser height
     *
     * @var int string
     */
    private $filebrowser_height = 300;

    /**
     * Filebrowser userrole
     *
     * @var string
     */
    private $filebrowser_userrole = '';

    /**
     * Id of form the editor belongs to
     *
     * @var string
     */
    private $form_id;

    /**
     * Hidden value form field
     *
     * @var Input
     */
    private $content_element;

    /**
     * Visible editor area div
     *
     * @var Div
     */
    private $edit_element;

    private $cfg;

    private $js;

    private $div;

    private $input;

    private $session;

    private $factory;

    public function __construct(HtmlFactory $factory)
    {
    	parent::__construct($factory);



        // our editor will be uesd as inline editor
        $this->edit_element = $this->div->addAttribute('contenteditable', 'true')->addData('url', $this->cfg->get('Core', 'url_tools'));

        // we need an hidden form field for content to post
        $this->content_element = $this->input->setType('hidden');

        $this->addData('control', 'editor');

        // Add needed CKE js library
        $this->js->file($this->cfg->get('Core', 'url_tools') . '/ckeditor/ckeditor.js?' . time());
    }

    public function getType()
    {
        return 'editor';
    }

    public function setValue($value)
    {
        $this->edit_element->setInner($value);
        return $this;
    }

    public function setId($id)
    {
        $this->edit_element->setId($id . '_editor');
        $this->content_element->setId($id);
        return $this;
    }

    public function setName($name)
    {
        // the hidden field is the field with the form content
        $this->content_element->setName($name);

        return $this;
    }

    public function setFormId($form_id)
    {
        $this->form_id = $form->_id;
        return $this;
    }

    public function setFilebrowserWidth($width)
    {
        $this->edit_element->addData('width', $width);
        return $this;
    }

    public function setFilebrowserHeight($height)
    {
        $this->edit_element->addData('height', $height);
        return $this;
    }

    /**
     * Sets user role and grants access on filebrowser
     *
     * @param string $role
     * @return \Core\Lib\Content\Html\Controls\Editor
     */
    public function setUserRole($role)
    {
        $this->session->set('KCFinder_Role', $role);
        $this->session->set('KCFinder_Access', true);

        return $this;
    }

    public function setUploadDir($uploaddir)
    {
        // filebrowser needs to stay in it's image uploadfolder
        $this->session->set('KCFinder_uploaddir', $uploaddir);
        return $this;
    }

    public function build()
    {
        $script = "
		if (typeof CKEDITOR !== undefined)
		{
			$(document).ready(function() {
				CKEDITOR.disableAutoInline = true;
				CKEDITOR.stylesSet.add( 'my_styles', [
					{ name: 'BS Code', element: 'code' },
					{ name: 'BS Jumbotron', element: 'div', attributes: { 'class': 'jumbotron' } },
				] );

				var editor = CKEDITOR.inline('{->edit_elementgetId()}', {
					stylesSet : 'my_styles',
					on : {
						instanceReady : function(){
							this.dataProcessor.writer.setRules('p', {
								indent : false,
								breakBeforeOpen : false,
								breakAfterOpen : false,
								breakBeforeClose : false,
								breakAfterClose : false
							});
					 =>  },
					},
					extraPlugins: 'bs-highlight,bs-jumbotron,bs-heading,bs-callout',
					language : smf_lang_dictionary,
					filebrowserBrowseUrl : $('#{->edit_elementgetId()}').data('url') + '/kcfinder/browse.php?opener=ckeditor&type=files',
					filebrowserUploadUrl : $('#{->edit_elementgetId()}').data('url') + '/kcfinder/upload.php?opener=ckeditor&type=files',
					filebrowserImageBrowseUrl : $('#{->edit_elementgetId()}').data('url') + '/kcfinder/browse.php?opener=ckeditor&type=images',
					filebrowserImageUploadUrl : $('#{->edit_elementgetId()}').data('url') + '/kcfinder/upload.php?opener=ckeditor&type=images',
				});
			});

			$('#{->form_id}').submit(function(e) {
				$('#{->content_elementgetId()}').val( editor.getData() );
				bootbox(editor.getData());
				e.preventDefault();
			});
		}";

        $this->js->script($script);

        $html = $this->content_element->build();
        $html .= $this->edit_element->build();

        return $html;
    }
}
