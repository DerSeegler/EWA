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
class Customer extends Page
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
        $pizzen = array();
		if (isset($_SESSION['bestellid'])) {
			$bestellid = $_SESSION["bestellid"];
			$sql = "SELECT * FROM bestelltepizza WHERE fBestellungID = $bestellid";
			$recordset = $this->_database->query ($sql);
			if (!$recordset)
				throw new Exception("Fehler in Abfrage: ".$this->database->error);
			// read selected records into result array
			$i = 0;
			while ($record = $recordset->fetch_assoc()) {
				$pizzen[$i] = $record;
				$i++;
			}
			$recordset->free();
		}
		return $pizzen;
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
        $pizzen = $this->getViewData();
        $this->generatePageHeader('Kunde');
        echo <<<EOT
<body>
    <section>
        <article>
            <h1>Kunde</h1>
				<form id="customerForm">
                <table>
                    <tr>
                        <th></th>
                        <th>bestellt</th>
                        <th>im Ofen</th>
                        <th>Fertig</th>
                        <th>unterwegs</th>
                    </tr>
					
EOT;
					$this->insert_rows($pizzen);
					echo <<<EOT
                </table>
				</form>
        </article>
        <article>
            <ul>
                <li>
                    <div class="big-button"><a href="Order.php">Neue Bestellung</a></div>
                </li>
            </ul>
        </article>
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
		session_start();
		if (isset($_POST["cart"]) && isset($_POST["adress"])) {
			$cart = $_POST['cart'];
			$adress = $this->_database->real_escape_string($_POST['adress']);
			$sql = "INSERT INTO bestellung (`Adresse`) VALUES (\"$adress\")";
			$this->_database->query($sql);
			$bestellid = $this->_database->insert_id;
			foreach($cart as $pizza) {
					$sql = "INSERT INTO bestelltepizza (`fBestellungID`, `fPizzaName`, `Status`) VALUES (\"$bestellid\", \"$pizza\", 0)";
					$this->_database->query($sql);
			}
			
			$_SESSION["bestellid"] = $bestellid;
			
			header('Location: Customer.php');
		}
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
            $page = new Customer();
            $page->processReceivedData();
            $page->generateView();
        }
        catch (Exception $e) {
            header("Content-type: text/plain; charset=UTF-8");
            echo $e->getMessage();
        }
    }
	
	private function insert_rows($pizzen)
	{
		$i = 0;
		foreach ($pizzen as $pizza) {
			$id = $pizza["PizzaID"];
			$name = $pizza["fPizzaName"];
			$status = $pizza["Status"];
			//echo "<form id=\"customerForm$i\">\n";
			echo "<tr>\n";
            echo "      <td>$name</td>\n";
			echo "      <td><input type=\"hidden\" name=\"$name\" value=\"$id\">\n";
            echo "      <input type=\"radio\" name=\"$name$i\" value=\"0\" disabled";
			if($status == 0)
				echo " checked ";
			echo "></td>\n";
            echo "      <td><input type=\"radio\" name=\"$name$i\" value=\"1\" disabled";
			if($status == 1)
				echo " checked ";
			echo "></td>\n";
            echo "      <td><input type=\"radio\" name=\"$name$i\" value=\"2\" disabled";
			if($status == 2)
				echo " checked ";
			echo "></td>\n";
            echo "      <td><input type=\"radio\" name=\"$name$i\" value=\"3\" disabled";
			if($status == 3)
				echo " checked ";
			echo "></td>\n";
            echo "</tr>\n";
			$i++;
		}
	}
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Customer::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >