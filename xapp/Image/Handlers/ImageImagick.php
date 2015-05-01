<?php
/**
 * Image manipulation functions for extension Imagick
 *
 *
 * Created by PhpStorm.
 * User: Luigi
 * Date: 5/03/14
 * Time: 12:38
 */
xapp_import("xapp.Image.Interface.ImageHandler");

class XApp_ImageImagick implements Xapp_ImageHandler {


    /**
     *  Default imagick resize filter
     */
    const IMAGICK_RESIZE_FILTER = imagick::FILTER_LANCZOS;

    /**
     * Opens an Image file
     *
     * @param $src
     * @param $errors
     * @return mixed
     */
    public static function open($src,&$errors) {

        $file_ext = XApp_Image_Utils::imageExtension($src);

        if (!array_key_exists($file_ext,XApp_Image_Utils::$compatibleImageTypes))
        {
            $errors[] = XAPP_TEXT_FORMATTED("IMAGE_TYPE_NOT_SUPPORTED",$file_ext);
            return false;
        }

        if (!file_exists($src))
        {
            $errors[] = XAPP_TEXT_FORMATTED("FILE_DOESNT_EXISTS").$src;
            return false;
        }

        $container = XApp_Image_Utils::$imageContainer;
        $container[XApp_Image_Utils::IMAGE_CONTAINER_SRC] = $src;
        $container[XApp_Image_Utils::IMAGE_CONTAINER_TYPE] = XApp_Image_Utils::$compatibleImageTypes[$file_ext];
        try
        {
            $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA] = new Imagick($src);
        }
        catch (Exception $e)
        {
            $errors[] = $e->getMessage();
        }

        return $container;
    }

    /**
     * Resizes the image and stores the results into container[IMAGE_CONTAINER_DATA]
     *
     *
     * @param $container
     * @param $options
     * @param $errors
     * @return mixed
     */
    public static function resize($container,$options,&$errors) {

        if ($container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] == null )
        {
            $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] = self::getImageSize($container[XApp_Image_Utils::IMAGE_CONTAINER_SRC]);
        }

        $currentSize = $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE];

        $newSize = XApp_Image_Utils::calcImageSize($currentSize,$options);

        try
        {
            $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA]->scaleImage($newSize->width, $newSize->height);
        }
        catch (Exception $e)
        {
            $errors[] = $e->getMessage();
        }

        $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] = $newSize;

        return $container;
    }

    /**
     * Returns the size of the Image
     *
     *
     * @param $src
     * @return mixed
     */
    public static function getImageSize($src) {

        $img = new Imagick($src);

        $size = new stdClass();
        $size -> width = $img->getImageWidth();
        $size -> height =$img->getImageHeight();
        return $size;
    }

    /**
     * Saves the image into the destination file
     *
     *
     * @param $container
     * @param $dst
     * @param $errors
     * @return mixed
     */
    public static function save($container,$dst,&$errors) {
        try
        {
            $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA]->writeImage($dst);
        }
        catch (Exception $e)
        {
            $errors[] = $e->getMessage();
        }
    }

    public static function output($container) {
       return $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA];
    }


}