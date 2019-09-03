<?php

namespace libs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MatchCommand extends Command
{

    protected function configure()
    {
        $this->addArgument('original image', InputArgument::REQUIRED, 'the original image file you want to match');
        $this->addArgument('match image', InputArgument::OPTIONAL, 'the image file you want to match');
        $this->addOption("ignore", "i",InputOption::VALUE_REQUIRED, "which image block you want to ignore to match, format is 'x1,y1,x2,y2:...");
        $this->setName("match");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $file1 = $input->getArgument("original image");
        $file2 = $input->getArgument("match image");

        if(!file_exists($file1)) {
            $output->writeln("file is not exist:" . $file1);
            return;
        }

        if(!file_exists($file2)) {
            $output->writeln("file is not exist:" . $file2);
            return;
        }

        if(!$this->getImageType($file1)) {
            $output->writeln("We don't support this file:" . $file1);
            return;
        }
        if(!$this->getImageType($file2)) {
            $output->writeln("We don't support this file:" . $file2);
            return;
        }

        $ignore_str_arr = explode(":", $input->getOption("ignore"));
        $j = 0;
        for($i=0;$i<count($ignore_str_arr);$i++) {
            $ignore = $ignore_str_arr[$i];
            if($ignore) {
                $arr = explode(",", $ignore);
                $ignore_arr[$j]["x1"] = $arr[0];
                $ignore_arr[$j]["y1"] = $arr[1];
                $ignore_arr[$j]["x2"] = $arr[2];
                $ignore_arr[$j]["y2"] = $arr[3];
                $j++;
            }
        }

       $output->write($this->matchImage($file1,$file2,$ignore_arr));
    }

    private function matchImage($file1, $file2, $ignore_blocks=array()) {
        $file_arr = pathinfo($file1);
        $i = $this->createImg($file1); //图片路径
        for ($x = 0; $x < imagesx($i); $x++) {
            for ($y = 0; $y < imagesy($i); $y++) {
                $rgb = imagecolorat($i, $x, $y);
                $r   = ($rgb >> 16) & 0xFF;
                $g   = ($rgb >> 8) & 0xFF;
                $b   = $rgb & 0xFF;
                $orignal[$x][$y] = $r . $g . $b;
            }
        }

        $diff = 0;
        $i = $this->createImg($file2); //图片路径

        for ($x = 0; $x < imagesx($i); $x++) {
            for ($y = 0; $y < imagesy($i); $y++) {
                if($this->ignoreBlock($ignore_blocks, $x, $y)){
                    continue;
                }
                $rgb = imagecolorat($i, $x, $y);
                $r   = ($rgb >> 16) & 0xFF;
                $g   = ($rgb >> 8) & 0xFF;
                $b   = $rgb & 0xFF;
                $pos = $r . $g . $b;
                if($pos != $orignal[$x][$y]){
                    $diff_pos[$x][$y] = 1;
                    $diff++;
                }
            }
        }

        if($diff > 0){
            $i = $this->createImg($file2); //图片路径
            $im = imagecreatetruecolor(1, 1);
            $red = imagecolorallocate($im, 255, 0, 0);

            foreach($diff_pos as $pos_x => $pos_arr) {
                foreach($pos_arr as $pos_y=>$y) {
                    imageline($i, $pos_x, $pos_y, $pos_x, $pos_y, $red);
                }
            }
            imagepng($i, $file_arr["dirname"]."/".$file_arr["filename"]."_diff.png");
        }

        return $diff;
    }

    private function createImg($file) {
        $type = $this->getImageType($file);
        $im = "";
        switch($type) {
            case "jpeg":
                $im = imagecreatefromjpeg($file);
                break;
            case "png":
                $im = imagecreatefrompng($file);
                break;
            case "bmp":
                $im = imagecreatefromwbmp($file);
                break;
        }

        return $im;
    }

    private function getImageType($file) {
        $type = mime_content_type($file);
        if($type == "image/jpeg") {
            return "jpeg";
        }

        if($type == "image/png") {
            return "png";
        }

        if($type == "image/bmp") {
            return "bmp";
        }

        return;
    }

    private function ignoreBlock($ignore_blocks, $x, $y){
        for($i=0;$i<count($ignore_blocks);$i++) {
            $ignore_block = $ignore_blocks[$i];
            $x1 = $ignore_block["x1"];
            $y1 = $ignore_block["y1"];
            $x2 = $ignore_block["x2"];
            $y2 = $ignore_block["y2"];

            if(is_numeric($x1) && is_numeric($x2) && is_numeric($y1) && is_numeric($y2)) {
                if($x >= $x1 && $x <= $x2 && $y >= $y1 && $y <= $y2) {
                    return true;
                }
            }
        }

        return;
    }
}
