<?php
/**
 * Image manipulation functions for extension GD
 *
 *
 * Created by PhpStorm.
 * User: Luigi
 * Date: 5/03/14
 * Time: 12:38
 */
xapp_import("xapp.Image.Interface.ImageHandler");

class XApp_ImageGD implements Xapp_ImageHandler
{

    /**
     * Opens an Image file
     *
     * @param $src
     * @param $errors
     * @return mixed
     */
    public static function open($src, &$errors)
    {
        $file_ext = XApp_Image_Utils::imageExtension($src);

        if (!array_key_exists($file_ext, XApp_Image_Utils::$compatibleImageTypes)) {
            $errors[] = XAPP_TEXT_FORMATTED("IMAGE_TYPE_NOT_SUPPORTED", $file_ext);
            return false;
        } else {
            $container = XApp_Image_Utils::$imageContainer;
            $container[XApp_Image_Utils::IMAGE_CONTAINER_SRC] = $src;
            $container[XApp_Image_Utils::IMAGE_CONTAINER_TYPE] = XApp_Image_Utils::$compatibleImageTypes[$file_ext];
            $img_function = "imagecreatefrom" . $container[XApp_Image_Utils::IMAGE_CONTAINER_TYPE];


            if (function_exists($img_function)) {
                $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA] = $img_function($src);
                return $container;
            } else {
                $errors[] = XAPP_TEXT_FORMATTED("IMAGE_MANIPULATION_FUNCTION_NOT_FOUND", $img_function);
                return false;
            }
        }
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
    public static function resize($container, $options, &$errors)
    {

        if ($container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] == null) {
            $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] = self::getImageSize($container[XApp_Image_Utils::IMAGE_CONTAINER_SRC]);
        }

        $currentSize = $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE];


        $newSize = XApp_Image_Utils::calcImageSize($currentSize, $options);

        $new_img_data = imagecreatetruecolor($newSize->width, $newSize->height);


        imagecopyresampled($new_img_data, $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA],
            0, 0, 0, 0,
            $newSize->width, $newSize->height,
            $currentSize->width, $currentSize->height
        );

        $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] = $newSize;
        $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA] = $new_img_data;

        return $container;
    }

    /**
     * @param $container
     * @param $options
     * @param $errors
     * @return mixed
     */
    public static function iconify($container, $options, &$errors)
    {
        // check that both width and height are provided
        $width = intval($options[XApp_Image_Utils::OPTION_WIDTH]);
        $height = intval($options[XApp_Image_Utils::OPTION_HEIGHT]);

        if ($width == 0 || $height == 0) {
            // if not, just resize
            return self::resize($container, $options, $errors);
        } else {
            // first - resize by longer dimension
            if ($container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] == null) {
                $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] = self::getImageSize($container[XApp_Image_Utils::IMAGE_CONTAINER_SRC]);
            }
            $currentSize = $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE];


            $options_for_resize = Array();
            $offset_x = $offset_y = 0;
            if ($currentSize->width > $currentSize->height) {
                $ratio = $width / $currentSize->width;
                $offset_y = ceil(($height - $currentSize->height * $ratio) / 2);
                $options_for_resize[XApp_Image_Utils::OPTION_WIDTH] = $width;
            } else {
                $ratio = $height / $currentSize->height;
                $offset_x = ceil(($width - $currentSize->width * $ratio) / 2);
                $options_for_resize[XApp_Image_Utils::OPTION_HEIGHT] = $height;
            }
            $container = self::resize($container, $options_for_resize, $errors);
            $newSize = $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE];

            // second - creates transparent image
            $thumb = imagecreatetruecolor($width, $height);
            imageSaveAlpha($thumb, true);
            ImageAlphaBlending($thumb, false);
            $tlo = imagecolorallocatealpha($thumb, 220, 220, 220, 127);
            imagefill($thumb, 0, 0, $tlo);

            // last - put image into square
            imagecopyresampled($thumb, $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA],
                $offset_x, $offset_y,
                0, 0,

                $newSize->width, $newSize->height,
                $width, $height);


            imagealphablending($thumb, false);

            $container[XApp_Image_Utils::IMAGE_CONTAINER_SIZE] = $newSize;
            $container[XApp_Image_Utils::IMAGE_CONTAINER_DATA] = $thumb;
            $container[XApp_Image_Utils::IMAGE_CONTAINER_TYPE] = XApp_Image_Utils::$compatibleImageTypes["png"];

            return $container;
        }


    }

    /**
     * Returns the size of the Image
     *
     *
     * @param $src
     * @return mixed
     */
    public static function getImageSize($src)
    {
        $file_info = getimagesize($src);

        $size = new stdClass();
        $size->width = $file_info[0];
        $size->height = $file_info[1];
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
    public static function save($container, $dst, &$errors)
    {
        // imagejpeg, imagegif of imagepng
        $save_function = "image" . $container[XApp_Image_Utils::IMAGE_CONTAINER_TYPE];

        if (function_exists($save_function)) {
            if ((@$save_function($container[XApp_Image_Utils::IMAGE_CONTAINER_DATA], $dst)) === false) {
                $errors[] = XAPP_TEXT_FORMATTED("IMAGE_NOT SAVED", $dst);
                return false;
            } else {
                return true;
            }
        } else {
            $errors[] = XAPP_TEXT_FORMATTED("IMAGE_MANIPULATION_FUNCTION_NOT_FOUND", $save_function);
            return false;
        }

    }

    public static function output($container)
    {
        // imagejpeg, imagegif of imagepng
        $save_function = "image" . $container[XApp_Image_Utils::IMAGE_CONTAINER_TYPE];

        if (function_exists($save_function)) {
            ob_start();
            $save_function($container[XApp_Image_Utils::IMAGE_CONTAINER_DATA]);
            return ob_get_clean();
        } else {
            $errors[] = XAPP_TEXT_FORMATTED("IMAGE_MANIPULATION_FUNCTION_NOT_FOUND", $save_function);
            return false;
        }
    }


}