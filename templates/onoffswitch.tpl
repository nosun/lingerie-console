<style type="text/css">
<!--
.onoffswitch {
    position: relative; width: 40px;
    -webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
}
.onoffswitch-checkbox {
    display: none;
}
.onoffswitch-label {
    display: block; overflow: hidden; cursor: pointer;
    border: 2px solid #999999; border-radius: 20px;
}
.onoffswitch-inner {
    width: 200%; margin-left: -100%;
    -moz-transition: margin 0.3s ease-in 0s; -webkit-transition: margin 0.3s ease-in 0s;
    -o-transition: margin 0.3s ease-in 0s; transition: margin 0.3s ease-in 0s;
}
.onoffswitch-inner:before, .onoffswitch-inner:after {
    float: left; width: 50%; height: 5px; padding: 0; line-height: 5px;
    font-size: 14px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold;
    -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;
}
.onoffswitch-inner:before {
    content: "";
    padding-left: 10px;
    background-color: #2FCCFF; color: #FFFFFF;
}
.onoffswitch-inner:after {
    content: "";
    padding-right: 10px;
    background-color: #EEEEEE; color: #999999;
    text-align: right;
}
.onoffswitch-switch {
    width: 15px; margin: -5px;
    background: #FFFFFF;
    border: 2px solid #999999; border-radius: 20px;
    position: absolute; top: 0; bottom: 0; right: 31px;
    -moz-transition: all 0.3s ease-in 0s; -webkit-transition: all 0.3s ease-in 0s;
    -o-transition: all 0.3s ease-in 0s; transition: all 0.3s ease-in 0s; 
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
    margin-left: 0;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
    right: 0px; 
}
-->
</style>
<div class="mingda-switch" style="float:left;padding-top:3px;">
<label style="float:left;font-weight:bold;padding:0 10px;">Details:</label>
<label style="float:left;">Hide</label>
<div class="switch-container">
<div class="onoffswitch">
    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="productonoffswitch">
    <label class="onoffswitch-label" for="productonoffswitch">
        <div class="onoffswitch-inner"></div>
        <div class="onoffswitch-switch"></div>
    </label>
</div>
</div>
<label style="float:left;">Show</label>
</div>