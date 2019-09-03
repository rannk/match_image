# PHP MATCH IMAGE

#### Description
match two pictures and find difference

#### Installation

1. download the code
2. go to your local code directory and do composer install

#### usage
match file1 file2 [-i | --ignore IGNORE]

-i IGNORE which image block you want to ignore to match. format is x1,y1,x2,y2 

example:

php /home/match_image/index.php match /tmp/file1.png /tmp/file2.png -i -i 0,0, 100,100