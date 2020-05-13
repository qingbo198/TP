<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <title>新增商品</title>
</head>
<style>

    .add_div {
        width: 400px;
        height: 500px;
        border: solid #ccc 1px;
        margin-top: 40px;
        margin-left: 170px;
        padding-left: 20px;
    }

    .file-list {
        width: 550px;
        height: 125px;
        display: none;
        list-style-type: none;
    }

    .file-list img {
        max-width: 70px;
        vertical-align: middle;
    }

    .file-list .file-item {
        margin-bottom: 10px;
        float: left;
        margin-left: 20px;
    }

    .file-list .file-item .file-del {
        display: block;
        margin-left: 20px;
        margin-top: 5px;
        cursor: pointer;
    }


</style>
<body>
<div class="add_div">
    <p>
        <span>分类：</span>
        <select name="select" style="width:120px;" id="catgory">
            <option value="0">顶级分类</option>
            <?php if(is_array($list)): foreach($list as $key=>$v): if($check == $v['id']): ?><option value="<?php echo ($v["id"]); ?>" selected><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option>
                    <?php else: ?>
                    <option value="<?php echo ($v["id"]); ?>"><?php echo str_repeat("&brvbar;--",$v["lev"]).$v["name"]?></option><?php endif; endforeach; endif; ?>
        </select>
    </p>
    <p>
        <span>名称：</span>
        <input type="text" name="" id="name" value=""/>
    </p>
    <p>
        <span>价格：</span>
        <input type="text" id="price"/>
    </p>
    <p>
        <span>图片：</span>
        <input type="file" name="" id="choose-file" multiple="multiple"/>
    </p>
    <p>
        <ul class="file-list ">

        </ul>
    </p>
    <p style="">
        <span style="display: block;">描述：</span>
        <textarea id="desc" rows="3" cols="16" style="margin-left: 50px;margin-top: -20px;">

        </textarea>
    </p>
    <p>
        <span>库存：</span>
        <input type="text" name="" id="stock" value=""/>
    </p>
    <p>
        <span>状态：</span>
        <input type="radio" name="status" value="1"/>显示
        <input type="radio" name="status" value="0"/>隐藏
    </p>
    <button style="cursor: pointer;margin-left: 150px;" href="javascript:;" id="upload">上传</button>

</div>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
<script>

    $(function () {
        ////////////////////////////////////////////////图片上传//////////////////////////////////////////////
        //声明变量
        var $button = $('#upload'),
            //选择文件按钮
            $file = $("#choose-file"),
            //回显的列表
            $list = $('.file-list'),
            //选择要上传的所有文件
            fileList = [];
        sendList = [];
        //当前选择上传的文件
        var curFile;
        // 选择按钮change事件，实例化fileReader,调它的readAsDataURL并把原生File对象传给它，
        // 监听它的onload事件，load完读取的结果就在它的result属性里了。它是一个base64格式的，可直接赋值给一个img的src.
        $file.on('change', function (e) {
            //上传过图片后再次上传时限值数量
            var numold = $('li').length;
            if (numold >= 6) {
                layer.alert('最多上传6张图片');
                return;
            }
            //限制单次批量上传的数量
            var num = e.target.files.length;
            var numall = numold + num;
            if (num > 6) {
                layer.alert('最多上传6张图片');
                return;
            } else if (numall > 6) {
                layer.alert('最多上传6张图片');
                return;
            }
            //layer.alert($("li").length);

            //原生的文件对象，相当于$file.get(0).files;//files[0]为第一张图片的信息;
            curFile = this.files;
            //curFile = $file.get(0).files;
            //console.log(curFile);
            //将FileList对象变成数组
            fileList = fileList.concat(Array.from(curFile));
            //console.log(fileList);
            for (var i = 0, len = curFile.length; i < len; i++) {
                reviewFile(curFile[i])
            }
            $('.file-list').fadeIn(2500);
        })


        function reviewFile(file) {
            //实例化fileReader,
            var fd = new FileReader();
            //获取当前选择文件的类型
            var fileType = file.type;
            //调它的readAsDataURL并把原生File对象传给它，
            fd.readAsDataURL(file);//base64
            //监听它的onload事件，load完读取的结果就在它的result属性里了
            fd.onload = function () {
                if (/^image\/[jpeg|png|jpg|gif]/.test(fileType)) {
                    $list.append('<li style="border:solid red px; margin:5px 5px;" class="file-item"><img src="' + this.result + '" alt="" height="70"><span class="file-del">删除</span></li>').children(':last').hide().fadeIn(2500);
                } else {
                    $list.append('<li class="file-item"><span class="file-name">' + file.name + '</span><span class="file-del">删除</span></li>')
                }

            }
        }

        //点击删除按钮事件：
        $(".file-list").on('click', '.file-del', function () {
            let parent = $(this).parent();
            let index = parent.index();
            fileList.splice(index, 1);
            parent.fadeOut(850, function () {
                parent.remove()
            });
            //$parent.remove()
            console.log(fileList);
            var linum = $('li').length;
            console.log(fileList);

            var filess = document.getElementById('choose-file');
            filess.value = '';

        });
        //点击上传按钮事件：
        $button.on('click', function () {
            var name = $('#name').val();
            var catgory = $('#catgory').val();
            var price = $('#price').val();
            var desc = $('#desc').val();
            var stock = $('#stock').val();
            var status = $("input[type='radio']:checked").val();
            var reg = /^[1-9]\d*$/;
            if (!(reg.test(stock))) {
                layer.alert('商品库存为整数!');
                return;
            }
            if (name == '') {
                layer.alert('请输入商品名称');
                return;
            } else if (status == null) {
                layer.alert('请输入商品显示状态');
                return;
            } else if (catgory == '') {
                layer.alert('请输入商品分类');
                return;
            } else if (price == '') {
                layer.alert('请输入商品价格');
                return;
            } else if (stock == '') {
                layer.alert('请输入商品库存');
                return;
            } else if (fileList.length == 0) {
                layer.alert('请选择商品图片');
                return;
            } else if (fileList.length > 6) {
                layer.alert('最多允许上传6张图片');
                return;
            } else {
                var formData = new FormData();
                for (var i = 0, len = fileList.length; i < len; i++) {
                    //console.log(fileList[i]);
                    formData.append('upfile[]', fileList[i]);
                }
                formData.append('name', name);
                formData.append('catgory', catgory);
                formData.append('price', price);
                formData.append('desc', desc);
                formData.append('stock', stock);
                formData.append('status', status);
                //console.log(formData);
                //return;
                $.ajax({
                    url: 'add',
                    type: 'post',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status == '1') {
                            layer.msg(data.content, {icon: 6},function () {
                                window.location.reload();
                            });
                            // window.location.reload();
                        } else if (data.status == '2') {
                            layer.msg(data.content, {icon: 1});
                            window.location.reload();
                        }
                    }
                })
            }
        })
    })


</script>
</body>
</html>