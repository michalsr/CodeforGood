<?php

$youtube_id = 'vwKlWZ9D-Bk';
$post_id = 94;
$x = get_post_meta($post_id, 'youtube', true);
if ($x) {$youtube_id = $x;}

?>
<div id="youtubevideo">
<p>Visit Our YouTube Channel 

<a href="http://www.youtube.com/user/MAAVorg#p/a/u/1/<?php echo $youtube_id?>" target="_blank" >[+]</a>
<br />Featured video:</p>
<object style="height: 190px; width: 200px"><param name="movie" value="http://www.youtube.com/v/<?php echo $youtube_id?>?version=3"><param name="allowFullScreen" value="true"><param name="allowScriptAccess" value="always"><embed src="http://www.youtube.com/v/<?php echo $youtube_id?>?version=3" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="200" height="190"></object>
</div>
