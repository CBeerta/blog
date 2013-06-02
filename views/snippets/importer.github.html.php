<?php echo $item->date->format('j M G:i'); ?>:
<a href="https://github.com/<?php echo $item->user; ?>"><?php echo $item->user; ?></a>
pushed to 
<b class="github"><?php echo $item->branch; ?></b>
at 
<a href="https://github.com/<?php echo $item->repo; ?>"><?php echo $item->repo; ?></a> 
