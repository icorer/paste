<html>
	<head>
		<meta charset="GB2312">
		<title>����ճ��</title>
	</head>
	<body>
		
		<?php
			if(!CheckCodes()) //�������Ϊ��
				exit();
				
		/*		
			if($_POST['code_type']=='php') //��ֹ���ֶ�������
			{
				$_POST['code_type']="phpcode";
			}
			*/
			
			$FileAddress=Create_src_File();  //����Դ���ļ� û�и�ʽ������·������ĩβ�е�
			//��ȡ��ص��ļ����� 
			/*
			�ļ�����·����;
			�ļ�������������
			�ļ���С
			
			*/
				
			$src_location=$FileAddress.$_POST['code_type']; //����Դ���ļ�·��
			$html_location=$FileAddress."html";//������ʾԴ��html�ļ�
			$src_content=file($src_location); //��ȡԴ���ļ�����
			$src_line=count($src_content); //��ȡԴ������
			$src_cols=0;
			$src_bytes=0;
			for($temp=0,$i=0;$i<$src_line;$i++)
			{
				$temp=strlen($src_content[$i]);
				$src_bytes+=$temp;
				if($temp>$src_cols)
					$src_cols=$temp;
			}
			$src_file_size=$src_bytes/1024; //�����ַ��������ļ���С ������λС��
			$src_file_size=number_format($src_file_size,2);
			Create_Html_doc($src_location,$html_location,$src_line,$src_cols,$src_file_size,$_POST['code_type']);
		?>
	</body>
</html>

<?php
	function Create_src_File() //����һ��Դ���ļ�·��
 	{
		if((isset($_POST['code_type']))&&($_POST['code_type']!=""))
		{
			//�����ļ�·��
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
	function CheckCodes(){  //�����û�����
		$status=1;
		if(!isset($_POST['code_centent'])||($_POST['code_centent']==""))
		{
			print "<script>alert(\"���󣺴������ݲ���Ϊ�գ�\")</script>";
			$status=0; //ȫ�ֱ���
		}
		if($status==0) //��֤ʧ�� ��ת
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
		if($src_type=='phpcode') //�ʵ��ָ��޸Ĵ��������ǩ����Ĵ�������
		{
			$src_type='php';
		}
	*/	
		//����ѹ���ĵ�
		$zip_location=str_replace($src_type,'zip',$src_location); //��Դ���ļ�·������ѹ��������·��
		$zip = new ZipArchive();
		$zip->open($zip_location, ZipArchive::CREATE);
		$zip->addFile($src_location,'source.'.$src_type);
		$zip->close();
		//ѹ���ļ��������
		
		
		$fhead=file("./example/head.php"); //��ȡģ��ͷ��
		$ffoot=file("./example/foot.php"); //��ȡģ��β��
		$fsrc=file($src_location); 
		$fhtml=fopen($html_location,"w"); //�½�html�����ļ�
		$zip_location=str_replace('code_doc/','',$zip_location);
		//���ģ��ͷ��
		for($i=0;$i<count($fhead);$i++)
		{
			$fhead[$i]=str_replace('source_file_lines',$src_lines,$fhead[$i]);
			$fhead[$i]=str_replace('source_file_cols',$src_cols,$fhead[$i]);
			$fhead[$i]=str_replace('source_file_size',$src_file_size,$fhead[$i]);
			$fhead[$i]=str_replace('source_file_type',$src_type,$fhead[$i]);
			$fhead[$i]=str_replace('source_file_location',$zip_location,$fhead[$i]);
			fwrite($fhtml,$fhead[$i]);
		}
		
		//������벿��
		$line1="";
		$line2="";
		for($i=0;$i<count($fsrc);$i++)
		{
			$fsrc[$i]=str_replace('<',"&lt;",$fsrc[$i]);
			$fsrc[$i]=str_replace(' ',"&nbsp;",$fsrc[$i]);
			$fsrc[$i]=str_replace('\t',"&nbsp;&nbsp;&nbsp;&nbsp;",$fsrc[$i]);
			$fsrc[$i]=str_replace('\n',"",$fsrc[$i]); //��ֹ���⻻��
			$line1=sprintf("<tr>\n<td class=\"blob-line-num js-line-number\" data-line-number=\"%d\"></td>",$i+1);
			$line2=sprintf(" <td  class=\"blob-line-code js-file-line\"><span class=\"%s\">%s</span></td>\n</tr>",$src_type,$fsrc[$i]);
			fwrite($fhtml,$line1);
			fwrite($fhtml,$line2);
		}
		
		
		//���β��
		for($i=0;$i<count($ffoot);$i++)
		{
			fwrite($fhtml,$ffoot[$i]);
		}
		fclose($fhtml);
		//ɾ��Դ���ļ�
		unlink($src_location);
		print "<script>window.location.href=\"".$html_location."\"</script>";
	}
?>
