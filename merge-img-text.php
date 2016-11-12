<?php
/**
 *
 * @copyright:    (c) 2016,
 * @author:       Reage
 * @version:      1.0 - 2016/11/12
 * @description:
 */

class MergeImgText {

    private $bg = '';


    static  $enchar = array('q',' ','w','e','r','t','y','u','i','o','p','a','s','d','f','g','h','j','k','l','z','x','c','v',
        'b','n','m','1','2','3','4','5','6','7','8','9','0','(',')','*','&','^','%','$','#','@','!','[',']',';','
                ','\'',',','.','/','{','}',':','"','?','>','<','\\','|',);

    public function __construct($bg) {
        $this->bg =   $this->load($bg);
    }

    /**
     * @param $src
     * @param $dst
     * @param $left
     * @param $top
     * @param int $width
     * @param int $height
     */
    public function mergeImg($src, $left, $top ,$width = 0, $height=0) {
        $src = $this->load($src);


        if(empty($width) || empty($height)) {
            $this->drawImg($src, $left, $top);
        } else {
            $this->drawImgSize($src, $left, $top, $width, $height);
        }

    }

    public function drawString($str, $fontsize, $color,  $left, $top, $width, $height,$align, $font) {
        $str = $this->getDrawTxt($str, $width, $fontsize);
        $loc = $this->getDrawTxtOffset(mb_strlen($str, 'utf8'),$fontsize,$left,$top,$width,$height, $align, $this->bg->getImageWidth());
        $style = array('size'=>$fontsize, 'gravity'=>$align, 'color'=>$color, 'font' => $font);
        $this->drawTxt($str, $style,$loc['x'], $loc['y']);
    }

    /**
     * 在图片画一个背景透明的矩形
     * @param $dst
     * @param $left
     * @param $top
     * @param $width
     * @param $height
     * @param $color
     * @param $opacity 0.1-1，值越大，透明度越低
     */
    public function drawRectOpacity($left, $top, $width, $height, $color, $opacity) {
        $draw = new \ImagickDraw();
        $draw->setFillColor($color);//设置填充色的颜色
        $draw->setFillAlpha($opacity);
        $draw->rectangle($left, $top, $width+$left, $height+$top);
        $this->bg->drawImage($draw);
    }

    /**
     * 在图片画一个矩形,可以设置矩形的边框和底色和透明度
     * @param $dst
     * @param $left
     * @param $top
     * @param $width
     * @param $height
     * @param $color   边框的颜色
     * @param $bgColor 矩形颜色
     * @param $opacity 0.1-1，值越大，透明度越低
     */
    public function drawRect($left, $top, $width, $height, $color, $bgColor = '' ,$opacity = 1) {
        $draw = new \ImagickDraw();
        if(!empty($bgColor)) {
            $draw->setFillColor($bgColor);//设置填充颜色
            $draw->setFillAlpha($opacity);
        } else {
            $draw->setFillAlpha(0);
        }
        $draw->setStrokeColor($color);
        $draw->rectangle($left, $top, $width+$left, $height+$top);
        $this->bg->drawImage($draw);
    }


    /** 将一个方形图片变为圆形图片，然后合并到主图上
     * @param $src
     * @param $left
     * @param $top
     * @param $radius  半径
     * @params $type   1,圆心位置   其他，左上角位置
     */
    public function mergeCircle($src, $left, $top, $radius, $type = 1) {
        $src = $this->load($src);
        $this->drawRoundImg($src);

        if($type == 1) {
            $left -= $radius;
            $top -= $radius;
        }
        $radius += $radius;
        $this->drawImgSize($src, $left, $top, $radius, $radius);

    }

    /***  在图片画一圆形
     * @param $left  圆心位置
     * @param $top   圆心位置
     * @param $radius  半径
     * @param $color   边框颜色
     * @param $bgColor 填充颜色
     * @param int $opacity 透明度
     */
    public  function drawCircle($left, $top, $radius, $color, $bgColor, $opacity = 1) {
        $draw = new \ImagickDraw();

        if(!empty($bgColor)) {
            $draw->setFillColor($bgColor);//设置填充颜色
            $draw->setFillAlpha($opacity);
        } else {
            $draw->setFillAlpha(0);
        }
        $draw->setStrokeColor($color);
        $draw->circle($left, $top, $left+$radius, $top);
        $this->bg->drawImage($draw);
    }

    /**
     * 图片的大小变成width*height
     * @param $width
     * @param $height
     */
    public function imgSize($width, $height) {
        $this->bg->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
    }

    public function output($filePath) {
        return $this->bg->writeImagesFile($filePath);
        //return $this->postImage($this->bg->getImagesBlob(), $this->bg->getFormat(), 'img', '/tmp/tmp.jpg');
    }
    //imagick方法

    /**
     * @param $file 加载一个图片
     * @return \Imagick
     */
    private function load($file) {
        $im = new Imagick($file);
        return $im;
    }

    /**
     * 将src画到dst图片的(x,y)的位置
     * @param $src
     * @param $dst
     * @param $x
     * @param $y
     */
    private function drawImg($src, $left, $top) {
        $this->bg->compositeImage($src, \Imagick::COMPOSITE_ATOP, $left, $top);
    }

    /**
     * 将src画到dst图片的(x,y)的位置,并且将src最总的大小变成width*height
     * @param $src
     * @param $dst
     * @param $left
     * @param $top
     * @param $width
     * @param $height
     */
    private function drawImgSize($src, $left, $top, $width, $height) {
        $src->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
        $this->drawImg($src, $left, $top);
    }

    /**
     * 在图片添加文字的
     * @param $dst
     * @param $txt 文字内容的。
     * @param $style $style=array($size='文字大小px','color'=>'颜色', 'gravity'=>'文字的摆放位置，left，right，center三个选项','font'=>'文字字体','under'=>'文字底色')
     * @param $left
     * @param $top
     */
    private function drawTxt($txt, $style, $left, $top) {
        $draw   = new \ImagickDraw();
        $draw->setFontSize($style['size']);//设置字体大小
        $draw->setFillColor($style['color']);//设置字体颜色
        switch($style['gravity']) {
            case 'left':// left;
                $draw->setGravity(\Imagick::GRAVITY_NORTHWEST);
                break;
            case 'right'://right
                $draw->setGravity(\Imagick::GRAVITY_NORTHEAST);
                break;
            default:// center;
                $draw->setGravity(\Imagick::CHANNEL_MAGENTA);
                break;
        }
        //$draw->setGravity($gravity);//设置水印位置
        //$draw->setGravity(Imagick::GRAVITY_NORTHEAST);//设置水印位置
        $style['font'] = ImagickConfig::getInstance()->getFonts($style['font']);
        $draw->setFont( $style['font']);
        if(isset($style['under'])) $draw->setTextUnderColor($style['under']);
        $this->bg->annotateImage($draw, $left, $top, 0, $txt);
    }


    /**
     * 得到一个圆形图片
     * @param $im
     */
    private function drawRoundImg($im) {
        $im->setFormat('png');
        $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
        $im->roundcorners($im->getImageWidth(), $im->getImageHeight());
    }

    private function getDrawTxt($txt, $width, $fontsize) {
        $clen = $width/$fontsize;//最多多少个字的
        $len = mb_strlen($txt, 'utf8'); //字符串长度
        $enlen = $txtlen = 0;//enlen字符行数，txtlen已经截取字符数,
        if($clen >= $len) { return $txt;}

        while($txtlen < $len) {
            $c = mb_strtolower(mb_substr($txt, $txtlen, 1, 'utf8'),'utf8');
            ++$txtlen;
            if(in_array($c, self::$enchar)) {
                ++$enlen;
            }
            if($txtlen-$enlen/4 >= $clen) {
                break;
            }
        }

        return mb_substr($txt, 0, $txtlen, 'utf8');

    }

    /**
     * 根据文字的对齐方式计算，文字现实开始位置的坐标
     * @param $len 文字的个数
     * @param $fontsize 用来计算topoffset
     * @param $left
     * @param $top
     * @param $width
     * @param $height
     * @param $align  文字的对齐方式
     * @param $imgwidth  图片的宽度，右对齐时使用的
     * @return array
     */
    private function getDrawTxtOffset($len, $fontsize, $left, $top, $width, $height, $align, $imgwidth) {
        $x = 2;
        $y = 0;
        //根据高度计算top误差
        $y = ($height - $fontsize)/2 - 2;
        if($y > 0) $y += $top; else $y = $top;
        if($len > ($width/$fontsize)) { $x = 2;}
        switch($align){
            case 'left':// left;
                $x += $left;
                break;
            case 'right'://right
                $x = ($imgwidth - $left - $width + $x);
                break;
            default:// center;
                $x = 0;
                break;
        }
        return array('x' => $x, 'y' => $y);
    }


    /**
     * 在图片画一个背景透明的矩形
     * @param $dst
     * @param $left
     * @param $top
     * @param $width
     * @param $height
     * @param $color
     * @param $opacity 0.1-1，值越大，透明度越低
     */
    private function drawRectImg($dst, $left, $top, $width, $height, $color, $opacity) {
        $draw = new ImagickDraw();
        $draw->setFillColor($color);//设置字体颜色
        $draw->setFillAlpha($opacity);
        $draw->rectangle($left, $top, $left+$width, $top+$height);
        $dst->drawImage($draw);
    }

    /**
     * 在图片画一个矩形边框
     * @param $dst
     * @param $left
     * @param $top
     * @param $width
     * @param $height
     * @param $color
     * @param $opacity 0.1-1，值越大，透明度越低
     */
    private function drawRectBorderImg($dst, $left, $top, $width, $height, $color) {
        $draw = new ImagickDraw();
        $draw->setStrokeColor($color);
        $draw->setFillColor('#ffffff');//设置字体颜色
        $draw->rectangle($left, $top, $left+$width, $top+$height);
        $dst->drawImage($draw);
    }

    /**
     * 在图片画一个矩形边框
     * @param $dst
     * @param $left
     * @param $top
     * @param $width
     * @param $height
     * @param $color
     */
    private function drawCircleBorderImg($dst,$left, $top, $width, $height, $color) {
        $draw = new ImagickDraw();

        $draw->setStrokeColor($color);
        $draw->setFillColor('#ffffff');//设置字体颜色
        $draw->circle($left, $top, $left+$width, $top);
        $dst->drawImage($draw);
    }


    /**
     * 根据规则将文字画到图片上
     * @param $txt
     * @param $row
     * @param $bg
     */
    private function drawTxtHandle($txt, &$row, $bg) {
        $style = array('size'=>$row['textfontsize'], 'color'=>$row['textfontcolor'],'gravity'=>$row['textalign'], 'font'=>$_SERVER['DOCUMENT_ROOT'].$row['textfontstyle']);
        //有背景颜色，画背景颜色
        if(!empty($row['textbgfontcolor'])  ) {
            $this->drawRectImg($bg,$row['left'], $row['top'], $row['width'],$row['height'], $row['textbgfontcolor'],$row['opacity']);
        }
        //根据高度计算top误差
        $txt = $this->getDrawTxt($txt, $row['width'], $row['textfontsize']);
        $location = $this->getDrawTxtOffset(strlen($txt), $row['textfontsize'], $row['left'], $row['top'], $row['width'], $row['height'], $row['textalign'], $bg->getImageWidth());
        $this->drawTxt($bg, $txt, $style, $location['x'], $location['y']);
    }


}