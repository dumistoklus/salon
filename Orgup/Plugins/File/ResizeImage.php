<?php

namespace Orgup\Plugins\File;

class ResizeImage {

    private $width;
    private $height;
    
    public function resizeimg( $filename, $smallimage, $w, $h, $ser = FALSE ) {

		// ��������� ����������� ������ �����������, ������� ����� ��������
		$ratio = $w/$h;
		// ������� ������� ��������� �����������
		$size_img = getimagesize($filename);


		// ���� ������� ������, �� ��������������� �� �����
		if (($size_img[0]<$w) && ($size_img[1]<$h)) {
			copy( $filename, $smallimage );
			return true;
		}
		// ������� ����������� ������ ��������� �����������
		$src_ratio=$size_img[0]/$size_img[1];

		// ����� ��������� ������� ����������� �����, ����� ��� ��������������� �����������
		// ��������� ��������� �����������

		if ( !$ser ) {
			if ($ratio<$src_ratio)
			{
				$h = $w/$src_ratio;
			}
			else
			{
				$w = $h*$src_ratio;
			}
		}

		// �������� ������ ����������� �� �������� ��������
		$dest_img = imagecreatetruecolor($w, $h);
		if ($size_img[2]==2)  $src_img = @imagecreatefromjpeg($filename);
		else if ($size_img[2]==1) $src_img = @imagecreatefromgif($filename);
		else if ($size_img[2]==3) $src_img = @imagecreatefrompng($filename);

		if ( !$src_img ) {
			return FALSE;
		}

		$sw = $sh = 0;
		if ( $ser ) {
			// ���� ������ ������ ������
			if ( $src_ratio < 1 ) {
				$sh = ceil( ( $size_img[1] - $size_img[0] ) / 2 );
				$size_img[1] -= 2*$sh;
			} else {
				$sw = ceil( ( $size_img[0] - $size_img[1] ) / 2 );
				$size_img[0] -= 2*$sw;
			}
		}

		// ������������ �����������	 �������� imagecopyresampled()
		// $dest_img - ����������� �����
		// $src_img - �������� �����������
		// $w - ������ ����������� �����
		// $h - ������ ����������� �����
		// $size_img[0] - ������ ��������� �����������
		// $size_img[1] - ������ ��������� �����������
        $this->width = $w;
        $this->height = $h;
		imagecopyresampled($dest_img, $src_img, 0, 0, $sw, $sh, $w, $h, $size_img[0], $size_img[1] );
		// ��������� ����������� ����� � ����
		if ($size_img[2]==2)  imagejpeg($dest_img, $smallimage);
		else if ($size_img[2]==1) imagegif($dest_img, $smallimage);
		else if ($size_img[2]==3) imagepng($dest_img, $smallimage);
		// ������ ������ �� ��������� �����������
		imagedestroy($dest_img);
		imagedestroy($src_img);
		return TRUE;
	}

    public function getNewHeight() {
        return $this->height;
    }

    public function getNewWidth(){
        return $this->width;
    }
}