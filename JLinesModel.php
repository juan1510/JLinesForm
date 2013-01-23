<?php
/**
 * Modelo con propiedades dinamicas para usar en las lineas 
 * cuando es modo editInline = false
 * 
 * @uses CFormModel
 * @author Juan David Rodriguez <juan.rodriguez@tramasoft.com>
 * @copyright 2013 - Juan David Rodriguez
 * @license New BSD License 
 */
class JLinesModel extends CFormModel{
    /**
     * Propiedad para almacenar los attributos de el modelo
     * @access private
     * @var array $attributes
     */
    private $attributes = array();
    /**
     * Reglas de Validacion del Modelo Base
     * @var array $rules
     */
    public $rules = array();
    
    /**
     * Reglas de Validacion del Modelo Base
     * @return array 
     */
    public function rules(){
        return $this->rules;
    }
	
    /**
     * Asignamos las nuevas propiedades
     * 
     * @param string $name
     * @param string $value 
     */
    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }
    /**
     * Metodo para retornar el valor de una propiedad
     * @param string $name
     * @return string 
     */
    public function __get($name) {
        if(array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }/*else{
            throw new CException(Yii::t('yii','Property "{class}.{property}" is not defined.',
			array('{class}'=>get_class($this), '{property}'=>$name)));
        }*/
    }
    /**
     * Verifica que exista el atributo
     * 
     * @param string $name
     * @return boolean 
     */
    public function __isset($name) {
        return isset($this->attributes[$name]);
    }
    /**
     * Elimina el atributo de la clase
     * @param string $name 
     */
    public function __unset($name) {
        unset($this->attributes[$name]);
    }
    /**
     *
     * @param string $name
     * @param array $arguments
     * @return mixed el retorno del metodo a llamar 
     */
    public function __call($name, $arguments) {
        echo "Calling object method '$name' "
             . implode(', ', $arguments). "<br>";
        if(class_exists('Closure', false) &&  $this->$name instanceof Closure)
			return call_user_func_array($this->$name, $parameters);
        
        throw new CException(Yii::t('yii','{class} and its behaviors do not have a method or closure named "{name}".',
			array('{class}'=>get_class($this), '{name}'=>$name)));
    }

}

?>
