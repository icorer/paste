<html>
	<head>
		<meta charset="GB2312">
		<title>代码粘贴</title>
	</head>
	<body>
		
		<?php
			if(!CheckCodes()) //如果代码为空
				exit();
				
		/*		
			if($_POST['code_type']=='php') //防止出现恶心运行
			{
				$_POST['code_type']="phpcode";
			}
			*/
			
			$FileAddress=Create_src_File();  //创建源码文件 没有格式名但有路径符号末尾有点
			//获取相关的文件数据 
			/*
			文件完整路径名;
			文件行数，列数；
			文件大小
			
			*/
				
			$src_location=$FileAddress.$_POST['code_type']; //构造源码文件路径
			$html_location=$FileAddress."html";//构造显示源码html文件
			$src_content=file($src_location); //获取源码文件内容
			$src_line=count($src_content); //获取源码行数
			$src_cols=0;
			$src_bytes=0;
			for($temp=0,$i=0;$i<$src_line;$i++)
			{
				$temp=strlen($src_content[$i]);
				$src_bytes+=$temp;
				if($temp>$src_cols)
					$src_cols=$temp;
			}
			$src_file_size=$src_bytes/1024; //根据字符数换算文件大小 保留两位小数
			$src_file_size=number_format($src_file_size,2);
			Create_Html_doc($src_location,$html_location,$src_line,$src_cols,$src_file_size,$_POST['code_type']);
		?>
	</body>
</html>

<?php
	function Create_src_File() //返回一个源码文件路径
 	{
		if((isset($_POST['code_type']))&&($_POST['code_type']!=""))
		{
			//构造文件路径
			$FileAddress="./code_doc/".time().".";
			
			$FileAddress_src=$FileAddress.$_POST['code_type'];
			$fsrc=fopen($FileAddress_src,"w");
			fwrite($fsrc,$_POST['code_centent']);
			fclose($fsrc);
			return $FileAddress;
		}
		
	}
?>

<?php
	function CheckCodes(){  //检验用户代码
		$status=1;
		if(!isset($_POST['code_centent'])||($_POST['code_centent']==""))
		{
			print "<script>alert(\"错误：代码内容不能为空！\")</script>";
			$status=0; //全局变量
		}
		if($status==0) //验证失败 跳转
		{
			print "<script>window.location.href=\"./index.php\"</script>";
			return false;
		}
		else
			return true;
	}
?>

<?php
	function Create_Html_doc($src_location,$html_location,$src_lines,$src_cols,$src_file_size,$src_type)
	{
	/*
		if($src_type=='phpcode') //适当恢复修改代码高亮标签里面的代码类型
		{
			$src_type='php';
		}
	*/	
		//创建压缩文档
		$zip_location=str_replace($src_type,'zip',$src_location); //用源码文件路径生成压缩包完整路径
		$zip = new ZipArchive();
		$zip->open($zip_location, ZipArchive::CREATE);
		$zip->addFile($src_location,'source.'.$src_type);
		$zip->close();
		//压缩文件操作完毕
		
		
		$fhead=file("./example/head.php"); //获取模板头部
		$ffoot=file("./example/foot.php"); //获取模板尾部
		$fsrc=file($src_location); 
		$fhtml=fopen($html_location,"w"); //新建html高亮文件
		$zip_location=str_replace('code_doc/','',$zip_location);
		//输出模板头部
		for($i=0;$i<count($fhead);$i++)
		{
			$fhead[$i]=str_replace('source_file_lines',$src_lines,$fhead[$i]);
			$fhead[$i]=str_replace('source_file_cols',$src_cols,$fhead[$i]);
			$fhead[$i]=str_replace('source_file_size',$src_file_size,$fhead[$i]);
			$fhead[$i]=str_replace('source_file_type',$src_type,$fhead[$i]);
			$fhead[$i]=str_replace('source_file_location',$zip_location,$fhead[$i]);
			fwrite($fhtml,$fhead[$i]);
		}
		
		//输出代码部分
		$line1="";
		$line2="";
		for($i=0;$i<count($fsrc);$i++)
		{
			$fsrc[$i]=str_replace('<',"&lt;",$fsrc[$i]);
			$fsrc[$i]=str_replace(' ',"&nbsp;",$fsrc[$i]);
			$fsrc[$i]=str_replace('\t',"&nbsp;&nbsp;&nbsp;&nbsp;",$fsrc[$i]);
			$fsrc[$i]=str_replace('\n',"",$fsrc[$i]); //禁止随意换行
			$line1=sprintf("<tr>\n<td class=\"blob-line-num js-line-number\" data-line-number=\"%d\"></td>",$i+1);
			$line2=sprintf(" <td  class=\"blob-line-code js-file-line\"><span class=\"%s\">%s</span></td>\n</tr>",$src_type,$fsrc[$i]);
			fwrite($fhtml,$line1);
			fwrite($fhtml,$line2);
		}
		
		
		//输出尾部
		for($i=0;$i<count($ffoot);$i++)
		{
			fwrite($fhtml,$ffoot[$i]);
		}
		fclose($fhtml);
		//删除源码文件
		unlink($src_location);
		print "<script>window.location.href=\"".$html_location."\"</script>";
	}
?>
