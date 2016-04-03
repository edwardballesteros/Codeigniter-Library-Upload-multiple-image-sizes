<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Media Library
 *
 * Custom Library for uploading multiple sizes
 *
 * Usage:
 *
 *
 *
 * @package     CodeIgniter
 * @category    Libraries
 * @author      Edwardson Ballesteros
 * @license     MIT
 * @version     1.0.0
 */

Class Media{

    public $upload_path = '';
    public $sizes =  array();
    public $allowed_types = "";

    public function __construct()
    {
        $this->upload_path = $this->upload_path !="" ?  $this->upload_path : "./media/";
        $this->sizes = count($this->sizes)> 0 ? $this->sizes : array( 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1200, 1400 );
        $this->allowed_types = $this->allowed_types !="" ? $this->allowed_types : "jpg|jpeg|JPG|JPEG|png|PNG";
    }

    /**
     * The $this->media->upload("file_name");
     * This method process the image uploaded base on different sizes (width) defined from above.
     **/

    public function upload ( $image_file_name = 'image_file' )
    {
        $original_path = "original/";
        $file_name =   rand() .  '_' . gmmktime(). "_" .  rand()  . '_ed';

        $config['upload_path'] = $this->upload_path . $original_path;
        $config['allowed_types'] = $this->allowed_types;
        $config['overwrite'] = true;
        $config['file_name'] = $file_name;

        $this->load->library('upload');
        $this->upload->initialize($config);

        if ( ! $this->upload->do_upload( $image_file_name ))
        {
            return array ( 'upload_errors' => $this->upload->display_errors('<p class="errors" />') );
        }
        else
        {
            $uploadData = $this->upload->data();
            $full_path = $uploadData['full_path'];

            $width = (int) $uploadData['image_width'];
            $height = (int) $uploadData['image_height'];

            foreach( $this->sizes as $size )
            {
                $target_folder =  $this->upload_path  .  $size . "/";

                if( !$this->folder_exist( $target_folder) )
                {
                    $this->folder_exist( $target_folder, true );
                }

                $thumb_config = array(
                    'image_name'				=> $full_path,
                    'path'						=> $target_folder,
                    'maintain_ratio'			=> true,
                    'width'						=> $size,
                    'height'					=> $height  / ( $width / $size )
                );

                $this->image_resize($thumb_config,100);
            }

            return $uploadData;
        }
    }

    /**
     * Check if the upload directory exis, If you passed the true value this will create the directory.
     **/

    protected function folder_exist( $path = "" , $write = false){
        if (!file_exists( $path )) {
            if($write){
                mkdir( $path , 0775, true);
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Lets do some resize right here
     **/
    protected function image_resize ( $data = array(), $percent = 100  )
    {
        $this->load->library('image_lib');

        $config = array (
            'source_image'					=> $data['image_name'],
            'new_image'						=> $data['path'],
            'maintain_ratio'				=> $data['maintain_ratio'],
            'width'							=> $data['width'],
            'height'						=> $data['height'],
            'quality'						=> "'".$percent."'",
            'config'						=> 'gd2'
        );

        $this->image_lib->initialize($config);
        $this->image_lib->resize();
        $this->image_lib->clear();
    }

    /**
     * Enables the use of CI super-global without having to define an extra variable.
     * I can't remember where I first saw this, so thank you if you are the original author.
     *
     * Copied from the Ion Auth library
     *
     * @access  public
     * @param   $var
     * @return  mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }
}