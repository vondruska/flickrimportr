<?
/**
 * Written for http://topbloglog.com/ 
 * Usage:
 * new Paging(string HREF BEFORE, string HREF AFTER, int CURRENT PAGE, int MAX PAGE[, 
 * Array('left' => int, 'center' => [3,5,7,...], 'right' => int)]
 * );
 * @author dzver <dzver@abv.bg>
 */

class Paging {
        protected $sep = ' ... ';
        protected $lblNext = 'Next &#187;';
        protected $lblPrev = '&#171; Previous';
        public function __construct($linkBefore, $linkAfter, $page, $maxpage, 
                $size = Array('left'=>5, 'center'=>3, 'right'=>3) ) 
        {
                $this->linkBefore = $linkBefore;
                $this->linkAfter = $linkAfter;
                $this->page = $page;
                $this->maxpage = $maxpage;
                $this->size = $size;

                $this->Run();
        }

        protected function PageLink($link, $selected, $text, $page = null){
                ?> <a href="<?=$link;?>" class="Page<?
                if ($selected) echo " current";
                ?>" onclick="view.paging(<?=($page == null) ? $text : $page;?>); return false;"><?=$text;?></a><?
        }

        protected function Run() {
                $halfsize = floor($this->size['center']/2);

                if ($this->page > 1)
                        $this->PageLink($this->linkBefore . ($this->page-1) . $this->linkAfter, false, $this->lblPrev, ($this->page-1));

                $callback = create_function('$n', 'return ($n<1)?1:($n>'.$this->maxpage.')?'.$this->maxpage.':$n;');
                $pages = array_unique(array_map($callback, array_merge(
                        range(1, $this->size['left']), 
                        range($this->page - $halfsize, $this->page + $halfsize),
                        range($this->maxpage - $this->size['right'] + 1, $this->maxpage)
                        )));
                sort($pages);

                $prevpage = 0;
                foreach ($pages as $i) {
                        if ($prevpage + 1 != $i) echo $this->sep;
                        $this->PageLink($this->linkBefore . $i . $this->linkAfter, $i == $this->page, $i);
                        $prevpage = $i;
                }

                if ($this->page < $this->maxpage) 
                        $this->PageLink($this->linkBefore . ($this->page + 1) . $this->linkAfter, false, $this->lblNext, ($this->page + 1));
        }
}
?>