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
class Driver extends Page
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
	
	protected $angebot = null;

    /**
     * Fetch all data that is necessary for later output.
     * Data is stored in an easily accessible way e.g. as associative array.
     *
     * @return none
     */
    protected function getViewData()
    {
		$this->angebot = array();
		$sql = "SELECT * FROM angebot";
		$recordset = $this->_database->query ($sql);
		if (!$recordset)
			throw new Exception("Fehler in Abfrage: ".$this->database->error);
		// read selected records into result array
		while ($record = $recordset->fetch_assoc()) {
			$this->angebot[$record["PizzaName"]] = $record["Preis"];
		}
		$recordset->free();
		
		$data = array();
        $bestellungen = array();
		$sql = "SELECT * FROM bestellung";
		$recordset = $this->_database->query ($sql);
		if (!$recordset)
			throw new Exception("Fehler in Abfrage: ".$this->database->error);
		// read selected records into result array
		$i = 0;
		while ($record = $recordset->fetch_assoc()) {
			$bestellungen[$i] = $record;
			$i++;
		}
		$recordset->free();
		
		$j = 0;
		foreach($bestellungen as $bestellung) {
			$pizzen = array();
			$id = $bestellung["BestellungID"];
			$sql = "SELECT * FROM bestelltepizza WHERE fBestellungID = $id";
			$recordset = $this->_database->query ($sql);
			if (!$recordset)
				throw new Exception("Fehler in Abfrage: ".$this->database->error);
			$i = 0;
			$finished = true;
			while ($record = $recordset->fetch_assoc()) {
				$pizzen[$i] = $record;
				if($record["Status"]<2 || $record["Status"]>3)
					$finished = false;
				$i++;
			}
			
			if($finished) {
				$ganzeBestellung = array();
				$ganzeBestellung["bestellung"] = $bestellung;
				$ganzeBestellung["pizzen"] = $pizzen;
				$data[$j] = $ganzeBestellung;
				$j++;
			}
			
			$recordset->free();
		}
		
		return $data;
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
        $data = $this->getViewData();
        $this->generatePageHeader('Fahrer');
        echo <<<EOT
<body>
    <section>
        <h1>Fahrer</h1>
EOT;
		$this->insert_bestellungen($data);
		echo <<<EOT
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
        if (isset($_POST["id"]) && isset($_POST["status"])) {
			$id = $_POST['id'];
			$status = $_POST['status'];
			$sql = "UPDATE bestelltepizza SET `Status` = $status WHERE `fBestellungID` = $id";
			$this->_database->query($sql);
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
            $page = new Driver();
            $page->processReceivedData();
            $page->generateView();
        }
        catch (Exception $e) {
            header("Content-type: text/plain; charset=UTF-8");
            echo $e->getMessage();
        }
    }
	
	private function insert_bestellungen($data)
	{
		$i = 0;
		foreach ($data as $ganzeBestellung) {
			$bestellung = $ganzeBestellung["bestellung"];
			$pizzen = $ganzeBestellung["pizzen"];
			$bestellungId = $bestellung["BestellungID"];
			$adresse = $bestellung["Adresse"];
			$adresse = htmlspecialchars($adresse);
			echo "<article class=\"order-delivery-box\">\n";
            echo "    <h2>$adresse</h2>\n";
            echo "    <p> ";
			$first = true;
			$status = 0;
			$preis = 0;
			foreach ($pizzen as $pizza) {
				if(!$first) {
					echo ", ";
				}
				else {
					$first = false;
					$status = $pizza["Status"];
				}
				echo $pizza["fPizzaName"];
				$preis = $preis + $this->angebot[$pizza["fPizzaName"]];
			}
			$preis = number_format($preis, 2);
			echo " </p>\n";
            echo "    <p> Preis: $preis € </p>\n";
            echo <<<EOT
			    <table>
                    <tr>
                        <th>gebacken</th>
                        <th>unterwegs</th>
                        <th>ausgeliefert</th>
                    </tr>
EOT;
			echo "	<form id=\"driverForm$i\" action=\"Driver.php\" accept-charset=\"UTF-8\" method=\"post\">\n";
            echo "        <tr>\n";
            echo "            <td><input type=\"hidden\" name=\"id\" value=\"$bestellungId\">\n<input type=\"radio\" name=\"status\" value=\"2\" onclick=\"document.forms['driverForm$i'].submit();\"";
			if($status == 2)
				echo " checked ";
			echo "></td>\n";
            echo "            <td><input type=\"radio\" name=\"status\" value=\"3\" onclick=\"document.forms['driverForm$i'].submit();\"";
			if($status == 3)
				echo " checked ";
			echo "></td>\n";
            echo "            <td><input type=\"radio\" name=\"status\" value=\"4\" onclick=\"document.forms['driverForm$i'].submit();\"></td>\n";
            echo "        </tr>";
			echo "    </form>";
            echo "    </table>";
            echo "</article>";
			
			$i++;
		}
	}
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Driver::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >