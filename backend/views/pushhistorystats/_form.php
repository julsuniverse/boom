<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
$dataproviderForcount  = $model->search();
//$dataproviderForcount->setPagination(false);
/* @var $this yii\web\View */
/* @var $searchModel backend\models\MemberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="member-index">
    <p>
         <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary','style'=>'float:right;']) ?>
    </p>
    <?php  echo $this->render('_search', ['model' => $model]); ?>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'PostList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),
        'filterModel' => $model,
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'header' => Html::checkBox('selection_all', false, [
                    'class' => 'select-on-check-all',
                    'style'=>'cursor:pointer',
                    'onclick'=>'checkBoxAllSelected()',
                    'id'=>'memberIds_all'
                ]),
                'checkboxOptions' => function ($model, $key, $index, $column)
                {
                    return ['value' => $model['MemberID'],
                            'class' => 'MemberIdsCheckbox',
                            'style'=>'cursor:pointer;'];
                }
            ],
            [
                'header' => 'Name',
                'attribute' => 'MemberName',
            ],
            'Email:email',
            [
                'header' => 'Age Group',
                'attribute' => 'DOB',
                'filter'=>Html::activeDropDownList($model, 'DOB',array(""=>"All","1"=>"18-30","2"=>"31-45","3"=>"46 & above"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            [
                'header' => 'Mobile Number',
                'attribute' => 'MobileNo',
            ],
            [
                'header' => 'Date Joined',
                'attribute' => 'DateJoined',
            ],
            [
                'header' => 'Artist',
                'attribute' => 'ArtistName',
                'filter'=>false,
            ],
        ],
    ]); ?>

    <p>
        <?= Html::button('Add Message', ['class' => 'btn btn-success','data-toggle'=>'modal','id' => 'add_msg_btn','onclick'=>'return messageValidate();']) ?>
    </p>
    <?php \yii\widgets\Pjax::end(); ?>
<input type="hidden" name="hidMessage" id="hidMessage" />
<input type="hidden" name="unselectedids" id="unselectedids" />
<input type="hidden" name="selectedids" id="selectedids" />
<input type="hidden" name="selectallhidden" id="selectallhidden" />
<input type="hidden" name="totalrecordcount" id="totalrecordcount" value="<?php //echo $dataproviderForcount->getTotalCount();?>" />
</div>

<?php

$this->registerJs('                 
    $(document).ready(function()
    {
        var send= 0;
        setCookie("pushcheckall","0", 365);
        checkAll();
        var checkall =  getCookie("pushcheckall");
        if(checkall == "1")
        {
            $("#selectallhidden").val(checkall);
        }
        else
        {
            $("#selectallhidden").val(checkall);
        }
    
        var results = [];
        var selectedres=	[];
        $(document).on("change", "input[name=\'selection[]\']", function ()
        {
            var item = $(this).val();
            var index = results.indexOf(item);
            var selindex = selectedres.indexOf(item);
            if ($(this).is(":checked") == false)
            {
                if (index == -1)
                {
                    results.push(item);
                }
                selectedres.splice(selindex, 1);
    
            }
            else
            {
                selectedres.push(item);
                if (index > -1)
                {
                    results.splice(index, 1);
                }
            }
            $("#unselectedids").val(results.join(","));
            $("#selectedids").val(selectedres.join(","));	    
           
        });
    });
    $(document).on("pjax:complete", function()
    {
        checkAll();
        $(".MemberIdsCheckbox").each(function()
	{
            var ids = $("#unselectedids").val().split(",");
            var selectedids = $("#selectedids").val().split(",");
            if(jQuery.inArray($(this).val(),ids) != -1)
            {
                    $(this).prop("checked",false);
            }
            if(jQuery.inArray($(this).val(),selectedids) != -1)
            {
                    $(this).prop("checked",true);
            }
	});
    });
');
    
?>

<script type="text/javascript">

function messageValidate()
{
    var checkedAtLeastOne = false;
     
    $('input[name="selection[]"]').each(function()
    {
	if ($(this).is(":checked"))
	{
	    checkedAtLeastOne = true;
	}
    });
    if(checkedAtLeastOne == false)
    {
	$("#add_msg_btn").removeAttr('data-target');
	alert("Please select atleast one member","");
	return false;
    }
    else
    {
	$("#add_msg_btn").attr('data-target','#pushnotificationModal');
	setTimeout(function() {
	    $("#Message").focus();
	    //$("#totalmem").html(getTotalMemberCount());
	    }, 100);
    }
}
function checkAll()
{
    var checkall =  getCookie("pushcheckall");
    if(checkall == "1")
    {
        $("input:checkbox").not(this).prop("checked", true);    
    }
    else
    {
        $("input:checkbox").not(this).prop("checked", false);    
    }
}
function checkBoxAllSelected()
{
    if($("#memberIds_all").is(':checked'))
    {
	setCookie("pushcheckall",'1', 365);
	$("#selectallhidden").val("1");
	$("#unselectedids").val("");
	$("#selectedids").val("");
	results = [];
	selectedres=[];
    }
    else
    {
	setCookie("pushcheckall",'0', 365);
	$("#selectallhidden").val("0");	   
    }
}
function setCookie(cname, cvalue, exdays)
{
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}
function validation()
{
    $('#hidMessage').val($('#Message').val());
    $("#pushnotificationModal").modal('hide');
    var send        =   1;
    var artistid    =   $("#membersearch-artistid").val();
    var message     = $("#Message").val();
    var isSelectAll = $("#selectallhidden").val();
    var selectedids = $("#selectedids").val();
    var unselectedids = $("#unselectedids").val();
    //var namesearch  =   $("input[name=nameGoesHere]").val();
    var datastring='artistid='+artistid+'&send='+send+'&message='+message+'&isSelectAll='+isSelectAll+'&selectedids='+selectedids+'&unselectedids='+unselectedids;
    $.ajax({
        type: 'POST',
        url: 'create',
        data:datastring,
        success:function(html){
          return true;
        },
        error:function(jqXHR, textStatus, errorThrown){
        },
        dataType:'html'
    });
}
</script>
<?php

Modal::begin([
    'header' => '<h2>Send Message</h2>',
    /*'toggleButton' => [
                'label' => '<i class="glyphicon glyphicon-plus"></i> Add Message',
                'class' => 'btn btn-success',
                'id' => 'add_msg_btn',
                'onclick'=>'return messageValidate();'
            ],*/
    'id'=>'pushnotificationModal',
    'closeButton' => ['id' => 'close-button'],
    'size' => '',
]);

echo '<div>
        <textarea rows="6" cols="300" name="Message" id="Message" maxlength="160" style="width:100%"></textarea>
        <br />
        <input style="margin-right:10px;" type="submit" name="send_notification" id="send_notification" class="btn btn-success" value="Send Notification" onclick="validation();" />
    </div>';

Modal::end();
?>
