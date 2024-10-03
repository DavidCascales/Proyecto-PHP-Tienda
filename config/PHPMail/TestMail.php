<?php

class TestMail
{

    public $propertiesFile;
    public $smtpConfig;

    public function __construct($propertiesFile)
    {

        $this->propertiesFile = $propertiesFile;
        $this->smtpConfig = $this->loadProperties();

    }
    public function loadProperties()
    {
        $properties = [];

        // Verifica si el archivo existe antes de intentar abrirlo
        if (!file_exists($this->propertiesFile)) {
            throw new Exception("El archivo de propiedades no se encuentra: " . $this->propertiesFile);
        }

        $lines = file($this->propertiesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            list($key, $value) = explode('=', $line, 2);
            $properties[trim($key)] = trim($value);
        }

        // Mostrar propiedades en la consola del navegador
        echo "<script>console.log(" . json_encode($properties) . ");</script>";
            
        return $properties;
    }

}


?>