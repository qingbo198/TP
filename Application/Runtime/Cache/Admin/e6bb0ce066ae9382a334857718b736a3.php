<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>新增文章分类</title>
    <link href="/TP/Public/Css/style.css" rel="stylesheet" type="text/css"/>
    <!--<script src="/TP/Public/Js/jquery-1.9.1.js" type="text/javascript"></script>
    <script src="/TP/Public/Js/jquery.validate.min.js" type="text/javascript"></script>
    <script src="/TP/Public/Js/my.js" type="text/javascript"></script>-->


</head>
<style>
    .error {
        color: red;
    }
</style>
</head>
<span>新增商品分类</span>&nbsp;&nbsp;&nbsp;
<a href="JavaScript:history.go(-1)">返回</a>
<body>
    <table class="tb_add">
        <tr>
            <td width="200px" align="right"><label for="username">所属分类</label></td>
            <td>
                <select name="select" style="width:120px;" id="catgory">
                    <option value="0">顶级分类</option>
                    <?php if(is_array($list)): foreach($list as $key=>$v): if($check == $v['id']): ?><option value="<?php echo ($v["id"]); ?>" selected><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option>
                            <?php else: ?>
                            <option value="<?php echo ($v["id"]); ?>"><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option><?php endif; endforeach; endif; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td align="right"><label for="name">分类名称:</label></td>
            <td><input id="name" type="text" style="width:115px;" value=""></td>
        </tr>
        <tr>
            <td colspan=2 align="center"><input class="submit" type="submit" id="submit" value="新增"></td>
        </tr>
    </table>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
<script>
    $(function () {
        $('#submit').click(function () {
            var catgory = $('#catgory').val();
            var name = $('#name').val();
            if(name==''){
                layer.alert('请输入商品分类名称');
                return;
            }
            $.ajax({
                url:'add',
                type:'post',
                data:{pid:catgory,name:name},
                dataType:'json',
                success:function(data){
                    if(data.status == '1'){
                        layer.msg(data.msg,{icon:1});
                    }else if(data.status == '0'){
                        layer.msg(data.msg,{icon:2});
                    }else if(data.status == '3'){
                        layer.msg(data.msg,{icon:2});
                    }
                }
            })
        })
    })
</script>
</body>
</html>