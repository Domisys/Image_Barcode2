<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */

/**
 * Image_Barcode2_ean13 class
 *
 * Renders EAN 13 barcodes
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Image
 * @package   Image_Barcode
 * @author    Didier Fournout <didier.fournout@nyc.fr>
 * @copyright 2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Image_Barcode
 */

require_once 'Image/Barcode2/Driver.php';
require_once 'Image/Barcode2/Common.php';

/**
 * Image_Barcode2_ean13 class
 *
 * Package which provides a method to create EAN 13 barcode using GD library.
 *
 * @category  Image
 * @package   Image_Barcode2
 * @author    Didier Fournout <didier.fournout@nyc.fr>
 * @copyright 2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Image_Barcode
 * @since     Image_Barcode2 0.4
 */
class Image_Barcode2_ean13 extends Image_Barcode2_Common implements Image_Barcode2_Driver
{
    /**
     * Barcode height
     *
     * @var integer
     */
    var $_barcodeheight = 50;

    /**
     * Font use to display text
     *
     * @var integer
     */
    var $_font = 2;  // gd internal small font

    /**
     * Bar width
     *
     * @var integer
     */
    var $_barwidth = 1;


    /**
     * Number set
     * @var array
     */
    var $_number_set = array(
           '0' => array(
                    'A' => array(0,0,0,1,1,0,1),
                    'B' => array(0,1,0,0,1,1,1),
                    'C' => array(1,1,1,0,0,1,0)
                        ),
           '1' => array(
                    'A' => array(0,0,1,1,0,0,1),
                    'B' => array(0,1,1,0,0,1,1),
                    'C' => array(1,1,0,0,1,1,0)
                        ),
           '2' => array(
                    'A' => array(0,0,1,0,0,1,1),
                    'B' => array(0,0,1,1,0,1,1),
                    'C' => array(1,1,0,1,1,0,0)
                        ),
           '3' => array(
                    'A' => array(0,1,1,1,1,0,1),
                    'B' => array(0,1,0,0,0,0,1),
                    'C' => array(1,0,0,0,0,1,0)
                        ),
           '4' => array(
                    'A' => array(0,1,0,0,0,1,1),
                    'B' => array(0,0,1,1,1,0,1),
                    'C' => array(1,0,1,1,1,0,0)
                        ),
           '5' => array(
                    'A' => array(0,1,1,0,0,0,1),
                    'B' => array(0,1,1,1,0,0,1),
                    'C' => array(1,0,0,1,1,1,0)
                        ),
           '6' => array(
                    'A' => array(0,1,0,1,1,1,1),
                    'B' => array(0,0,0,0,1,0,1),
                    'C' => array(1,0,1,0,0,0,0)
                        ),
           '7' => array(
                    'A' => array(0,1,1,1,0,1,1),
                    'B' => array(0,0,1,0,0,0,1),
                    'C' => array(1,0,0,0,1,0,0)
                        ),
           '8' => array(
                    'A' => array(0,1,1,0,1,1,1),
                    'B' => array(0,0,0,1,0,0,1),
                    'C' => array(1,0,0,1,0,0,0)
                        ),
           '9' => array(
                    'A' => array(0,0,0,1,0,1,1),
                    'B' => array(0,0,1,0,1,1,1),
                    'C' => array(1,1,1,0,1,0,0)
                        )
        );

    var $_number_set_left_coding = array(
           '0' => array('A','A','A','A','A','A'),
           '1' => array('A','A','B','A','B','B'),
           '2' => array('A','A','B','B','A','B'),
           '3' => array('A','A','B','B','B','A'),
           '4' => array('A','B','A','A','B','B'),
           '5' => array('A','B','B','A','A','B'),
           '6' => array('A','B','B','B','A','A'),
           '7' => array('A','B','A','B','A','B'),
           '8' => array('A','B','A','B','B','A'),
           '9' => array('A','B','B','A','B','A')
        );

    /**
     * Draws a EAN 13 image barcode
     *
     * @param string $text A text that should be in the image barcode
     *
     * @return image            The corresponding Interleaved 2 of 5 image barcode
     *
     * @access public
     *
     * @author     Didier Fournout <didier.fournout@nyc.fr>
     * @todo       Check if $text is number and len=13
     *
     */
    public function draw($text)
    {
        // Calculate the barcode width
        $barcodewidth = (strlen($text)) * (7 * $this->_barwidth)
            + 3 * $this->_barwidth  // left
            + 5 * $this->_barwidth  // center
            + 3 * $this->_barwidth // right
            + $this->writer->imagefontwidth($this->_font) + 1
            ;

        $barcodelongheight = (int)($this->writer->imagefontheight($this->_font) / 2)
            + $this->_barcodeheight;

        // Create the image
        $img = $this->writer->imagecreate(
            $barcodewidth,
            $barcodelongheight + $this->writer->imagefontheight($this->_font) + 1
        );

        // Alocate the black and white colors
        $black = $this->writer->imagecolorallocate($img, 0, 0, 0);
        $white = $this->writer->imagecolorallocate($img, 255, 255, 255);

        // Fill image with white color
        $this->writer->imagefill($img, 0, 0, $white);

        // get the first digit which is the key for creating the first 6 bars
        $key = substr($text, 0, 1);

        // Initiate x position
        $xpos = 0;

        // print first digit
        $this->writer->imagestring(
            $img,
            $this->_font, 
            $xpos, 
            $this->_barcodeheight, 
            $key, 
            $black
        );

        $xpos= $this->writer->fontwidth($this->_font) + 1;

        // Draws the left guard pattern (bar-space-bar)
        // bar
        $this->writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $this->_barwidth - 1,
            $barcodelongheight, 
            $black
        );
        $xpos += $this->_barwidth;
        // space
        $xpos += $this->_barwidth;
        // bar
        $this->writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $this->_barwidth - 1,
            $barcodelongheight, 
            $black
        );
        $xpos += $this->_barwidth;

        // Draw left $text contents
        $set_array = $this->_number_set_left_coding[$key];
        for ($idx = 1; $idx < 7; $idx ++) {
            $value = substr($text, $idx, 1);

            $this->writer->imagestring(
                $img, 
                $this->_font, 
                $xpos + 1, 
                $this->_barcodeheight, 
                $value, 
                $black
            );

            foreach ($this->_number_set[$value][$set_array[$idx - 1]] as $bar) {
                if ($bar) {
                    $this->writer->imagefilledrectangle(
                        $img,
                        $xpos,
                        0,
                        $xpos + $this->_barwidth - 1,
                        $barcodelongheight, 
                        $black
                    );
                }
                $xpos += $this->_barwidth;
            }
        }

        // Draws the center pattern (space-bar-space-bar-space)
        // space
        $xpos += $this->_barwidth;
        // bar
        $this->writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $this->_barwidth - 1,
            $barcodelongheight, 
            $black
        );
        $xpos += $this->_barwidth;
        // space
        $xpos += $this->_barwidth;
        // bar
        $this->writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $this->_barwidth - 1,
            $barcodelongheight, 
            $black
        );
        $xpos += $this->_barwidth;
        // space
        $xpos += $this->_barwidth;


        // Draw right $text contents
        for ($idx = 7; $idx < 13; $idx ++) {
            $value = substr($text, $idx, 1);

            $this->writer->imagestring(
                $img,
                $this->_font,
                $xpos + 1,
                $this->_barcodeheight,
                $value,
                $black
            );

            foreach ($this->_number_set[$value]['C'] as $bar) {
                if ($bar) {
                    $this->writer->imagefilledrectangle(
                        $img,
                        $xpos,
                        0,
                        $xpos + $this->_barwidth - 1,
                        $barcodelongheight, 
                        $black
                    );
                }
                $xpos += $this->_barwidth;
            }
        }

        // Draws the right guard pattern (bar-space-bar)
        // bar
        $this->writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $this->_barwidth - 1,
            $barcodelongheight, 
            $black
        );
        $xpos += $this->_barwidth;
        // space
        $xpos += $this->_barwidth;
        // bar
        $this->writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $this->_barwidth - 1,
            $barcodelongheight, 
            $black
        );

        return $img;
    } // function create

} // class

?>
