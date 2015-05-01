<?php

/**
 * Interface Xapp_ImageHandler
 *
 * interface for image manipulation handlers (with extensions GD, Imagick, etc.)
 *
 */
interface Xapp_ImageHandler
{
    /**
     * Opens an Image file returns the image container
     *
     * @param $src
     * @param $errors
     * @return mixed
     */
    public static function open($src,&$errors);


    /**
     * Resizes the image and stores the results into container[IMAGE_CONTAINER_DATA]
     *
     *
     * @param $container
     * @param $options
     * @param $errors
     * @return mixed
     */
    public static function resize($container,$options,&$errors);

    /**
     * Returns the size of the Image
     *
     *
     * @param $src
     * @return mixed
     */
    public static function getImageSize($src);

    /**
     * Saves the image into the destination file
     *
     *
     * @param $container
     * @param $dst
     * @param $errors
     * @return mixed
     */
    public static function save($container,$dst,&$errors);

    /**
     * Returns the image data
     *
     *
     * @param $container
     * @return string
     */
    public static function output($container);



}