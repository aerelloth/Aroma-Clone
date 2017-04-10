<?php 

class View
{
	private $path;
	private $data;
	private $templatePath;
	
	public function __construct($viewPath, $viewData=[], $templatePath=null)
	{
		$this -> path = $viewPath;
		$this -> data = $viewData;
		$this -> templatePath = $templatePath;
	}

	public function generate()
	{
		extract ($this -> data);
		if ($this -> templatePath === null)
		{
			include SERVER_ROOT.'application/views/'.$this -> path;
		}
		else 
		{
			ob_start();
			include SERVER_ROOT.'application/views/'.$this -> path;
			$viewContent = ob_get_clean();

			include SERVER_ROOT.'application/views/templates/'.$this -> templatePath;
		}
	}
}

 ?>