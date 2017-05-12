<div class="feedback-container">
<div class="feedback-block"><div class="feedback-title"><b style="color:#000;white-space:nowrap;font-size:x-small;" class="tiny">Help us improve our content by your votes:</b>&nbsp;</div>
<div>
<a style="font-size:1px;" name=""> </a>
<span class="crVotingButtons">

<?php if(isset($_SESSION['ratingInfo']) && in_array($url_key, $_SESSION['ratingInfo'])):?>
<span class="votingMessage">You have already voted for this article.</span>
</span>
<?php else:?>
<nobr><span class="votingPrompt">Was this article helpful to you?&nbsp;</span><a href="" class="votingButtonReviews ishelpful" rel="nofollow"><span>Yes</span></a>
<a href="" class="votingButtonReviews isunhelpful" rel="nofollow"><span>No</span></a></nobr>
<span class="votingMessage"></span>
<?php endif;?>
</div></div>

<div class="w-fbc" style="display:none;">
<span class="arrow"></span>
<a class="fbc-close" title="close">No Feedback</a>
<div class="feedbackComment" id="feedback-contents">
<div>
<p>Help us improve the content better:</p>
<form id="feedback-form" name="feedback-form">
<div class="textareaHolder" style="font-family:verdana;">
<textarea style="" class="default" name="fbc-content">Add your thoughts here...</textarea>
</form>
</div>
<input type="button" id="fbc-submit" class="btn-send" name="fbc-submit"/>
</div>
</div>
</div>

<div style="float: left; padding-top: 3px;"><div style="padding-left:5px;">

<div style="white-space:nowrap;padding-top:18px;"><a href="#discussion-head" class="discussion_view">View All Discussions(<?php echo count($all_discussions);?>)</a></div></div></div><div style="clear:both;"></div>
</div>