$(function(){
    //layer 删除弹框
    $('.button_del').click(function(){
        var id = $(this).attr("data-id");
        layer.confirm('确定要删除吗？', {
                title : ['删除' , true],
                btn: ['确定','取消'] //按钮
        }, function(){
            $.ajax({  //ajax传值
                type: "POST",
                url:"del",
                data:{id:id},
                dataType: 'json',
                success:function(data){ //返回data信息		
                    if(data.code > 0){					
                            layer.msg('删除成功',{icon:1},function(){
                                    window.location.reload();
                            });
                    }else{
                            layer.msg('删除失败',{icon:2},function(){
                                    window.location.reload();
                            });
                    }
                }
            });
        });	
    })
	
	//日期插件
 laydate({
            elem: '#date'
        });
	
        //table鼠标拾取变色
        $('.tb tr').mouseover(function(){
            $(this).addClass('tr_back');
        }).mouseleave(function(){
            $(this).removeClass('tr_back');
        })
})

