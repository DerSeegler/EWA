<?php	// UTF-8 marker äöüÄÖÜß€
/**
 * Class PageTemplate for the exercises of the EWA lecture
 * Demonstrates use of PHP including class and OO.
 * Implements Zend coding standards.
 * Generate documentation with Doxygen or phpdoc
 * 
 * PHP Version 5
 *
 * @category File
 * @package  Pizzaservice
 * @author   Bernhard Kreling, <b.kreling@fbi.h-da.de> 
 * @author   Ralf Hahn, <ralf.hahn@h-da.de> 
 * @license  http://www.h-da.de  none 
 * @Release  1.2 
 * @link     http://www.fbi.h-da.de 
 */

// to do: change name 'PageTemplate' throughout this file
require_once './Page.php';
require_once './OrderMenu.php';
require_once './OrderCart.php';

/**
 * This is a template for top level classes, which represent 
 * a complete web page and which are called directly by the user.
 * Usually there will only be a single instance of such a class. 
 * The name of the template is supposed
 * to be replaced by the name of the specific HTML page e.g. baker.
 * The order of methods might correspond to the order of thinking 
 * during implementation.
 
 * @author   Bernhard Kreling, <b.kreling@fbi.h-da.de> 
 * @author   Ralf Hahn, <ralf.hahn@h-da.de> 
 */
class Order extends Page
{
    // to do: declare reference variables for members 
    // representing substructures/blocks
    
    /**
     * Instantiates members (to be defined above).   
     * Calls the constructor of the parent i.e. page class.
     * So the database connection is established.
     *
     * @return none
     */
    protected function __construct() 
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }
    
    /**
     * Cleans up what ever is needed.   
     * Calls the destructor of the parent i.e. page class.
     * So the database connection is closed.
     *
     * @return none
     */
    protected function __destruct() 
    {
        parent::__destruct();
    }

    /**
     * Fetch all data that is necessary for later output.
     * Data is stored in an easily accessible way e.g. as associative array.
     *
     * @return none
     */
    protected function getViewData()
    {
        $angebote = array();
		$sql = "SELECT * FROM angebot";
		$recordset = $this->_database->query ($sql);
		if (!$recordset)
			throw new Exception("Fehler in Abfrage: ".$this->database->error);
		// read selected records into result array
		$i = 0;
		while ($record = $recordset->fetch_assoc()) {
			$angebote[$i] = $record;
			$i++;
		}
		$recordset->free();
		return $angebote;
    }
    
    /**
     * First the necessary data is fetched and then the HTML is 
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if avaialable- the content of 
     * all views contained is generated.
     * Finally the footer is added.
     *
     * @return none
     */
    protected function generateView() 
    {
        $angebote = $this->getViewData();
        $this->generatePageHeader('Bestellung');
        echo <<<EOT
<body>
<script type="text/javascript">
    function init() {
        document.getElementById("btnDelAll").onclick = deleteAll;
        document.getElementById("btnDelSel").onclick = deleteSelected;
    }

    function addItem(sender) {
		this.init();
        var cart = document.getElementById("cart");
        var cartItem = document.createElement("option");
        cartItem.text = sender.getAttribute("data-name");
        cart.appendChild(cartItem);
        updateSum();
    }
  
    function checkForm() {
        var cartOptions = document.getElementById("cart").length;
        var adress = document.getElementById("txtAdress").value;
        if (cartOptions > 0 && adress != "")
        {
            var cart = document.getElementById("cart");
            var cartItems = cart.options;
            var i = cartItems.length;
            while (i--) {
                var current = cartItems[i];
                if (!current.selected) {
                    current.selected = true;
                }
            }
      
            return true;
        }

        return false;
    }
  
    function deleteAll() {
        var cart = document.getElementById("cart");
        while (cart.firstChild)
        {
            cart.removeChild(cart.firstChild);
        }

        updateSum();
    }
  
    function deleteSelected() {
        var cart = document.getElementById("cart");
        var cartItems = cart.options;
        var i = cartItems.length;
        while (i--) {
            var current = cartItems[i];
            if (current.selected) {
                cart.removeChild(current);
            }
        }

        updateSum();
    }
  
    function mouseOut(sender) {
        sender.src = "images/pizza.png";
    }
  
    function mouseOver(sender) {
        sender.src = "images/pizza-hover.png";
    }
  
    function updateSum() {
		var angebote = {};
		
EOT;
		foreach ($angebote as $angebot) {
			$name = $angebot["PizzaName"];
			$preis = number_format($angebot["Preis"], 2);
			echo "angebote[\"$name\"] = $preis;\n";	
		}
		echo <<<EOT
        var cart = document.getElementById("cart");
        var sum = 0.0;
        for (i=0; i<cart.length; i++)
        {
            sum += parseFloat(angebote[cart.options[i].text]);
        }
    
        var sumP = document.getElementById("sum");
        sumP.innerHTML = sum.toFixed(2) + "€";
    }
</script>
<section>
    <h1>Bestellung</h1>
EOT;
            $blockMenu = new OrderMenu($this->_database);
            $blockMenu->generateView('order-selection', 'order-selection', $angebote);
            echo <<<EOT
    <form id="orderForm" action="customer.php" accept-charset="UTF-8" method="post" onsubmit="return checkForm()">
EOT;
                $blockCart = new OrderCart($this->_database);
                $blockCart->generateView('order-submission', 'order-submission');
                echo <<<EOT
    </form>
</section>
</body>
EOT;
        $this->generatePageFooter();
    }
    
    /**
     * Processes the data that comes via GET or POST i.e. CGI.
     * If this page is supposed to do something with submitted
     * data do it here. 
     * If the page contains blocks, delegate processing of the 
	 * respective subsets of data to them.
     *
     * @return none 
     */
    protected function processReceivedData() 
    {
        parent::processReceivedData();
        // to do: call processReceivedData() for all members
    }

    /**
     * This main-function has the only purpose to create an instance 
     * of the class and to get all the things going.
     * I.e. the operations of the class are called to produce
     * the output of the HTML-file.
     * The name "main" is no keyword for php. It is just used to
     * indicate that function as the central starting point.
     * To make it simpler this is a static function. That is you can simply
     * call it without first creating an instance of the class.
     *
     * @return none 
     */    
    public static function main() 
    {
        try {
            $page = new Order();
            $page->processReceivedData();
            $page->generateView();
        }
        catch (Exception $e) {
            header("Content-type: text/plain; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Order::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >