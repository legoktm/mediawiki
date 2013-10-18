<?php
require_once("HTTPFetch.php");
require_once("MementoParse.php");
require_once("TestSupport.php");
require_once('PHPUnit/Extensions/TestDecorator.php');

error_reporting(E_ALL | E_NOTICE | E_STRICT);

class MementoTest extends PHPUnit_Framework_TestCase {

	public static $instance = 0;

	public static function setUpBeforeClass() {
		global $sessionCookieString;

		$sessionCookieString = authenticateWithMediawiki();
	}

	public static function tearDownAfterClass() {

		logOutOfMediawiki();
	}

	protected function setUp() {
		self::$instance++;
	}

	public function StandardEntityTests( $entity ) {
		# To catch any PHP errors that the test didn't notice
		$this->assertFalse(strpos($entity, "<b>Fatal error</b>"), "Fatal error discovered in output");

		# To catch any PHP notices that the test didn't notice
		$this->assertFalse(strpos($entity, "<b>Notice</b>"), "PHP notice discovered in output");

		# To catch any PHP notices that the test didn't notice
		$this->assertFalse(strpos($entity, "<b>Warning</b>"), "PHP warning discovered in output");
		
		if ($entity) {
		    $this->fail("302 response should not contain entity for URI $URIG");
		}
	}

    public function 302StyleTimeGateResponseCommonTests(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {

		global $sessionCookieString;

		$uagent = "Memento-Mediawiki-Plugin/Test";
		$outputfile = __CLASS__ . '.' . __FUNCTION__ . '.' . $IDENTIFIER . '.txt';
		$debugfile = __CLASS__ . '.' . __FUNCTION__ . '.' . $IDENTIFIER . '-debug.txt';

		$curlCmd = "curl -v -s -A '$uagent' -b '$sessionCookieString' -k -i -H 'Accept-Datetime: $ACCEPTDATETIME' -H \"X-TestComment: $COMMENT\" --url \"$URIG\"";
		#echo '[' . $curlCmd . "]\n";
		$response = `$curlCmd 2> $debugfile | tee -a "$outputfile"`;
		file_put_contents( $outputfile, "\n#########################################\n", FILE_APPEND );
		file_put_contents( $debugfile, "\n#########################################\n", FILE_APPEND );

		$headers = extractHeadersFromResponse($response);
		$statusline = extractStatuslineFromResponse($response);
		$entity = extractEntityFromResponse($response);

		$this->assertEquals("302", $statusline["code"]);

		$this->assertArrayHasKey('Link', $headers, "No Link Header present");
		$this->assertArrayHasKey('Vary', $headers, "No Vary Header present");
		$this->assertArrayHasKey('Location', $headers, "No Location Header present");

		$this->assertEquals($URIM, $headers['Location'], "Location header contains incorrect Memento URI"); 

		$varyItems = extractItemsFromVary($headers['Vary']);
		$this->assertContains('Accept-Datetime', $varyItems, "Accept-Datetime not present in Vary Header");

		$relations = extractItemsFromLink($headers['Link']);

		$this->assertArrayHasKey('original latest-version timegate', $relations, "original latest-version timegate relation not present in Link Header");
		$this->assertEquals($URIG, $relations['original latest-version timegate']);

		$this->assertArrayHasKey('timemap', $relations, "timemap relation not present in Link Header");
		$this->assertEquals($URIG, $relations['timemap']);

		$this->StandardEntityTests($entity);

		return $response;
	}

	public function 200StyleTimeGateMementoResponseCommonTests(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
			) {

		global $sessionCookieString;

		$uagent = "Memento-Mediawiki-Plugin/Test";

		$outputfile = __CLASS__ . '.' . __FUNCTION__ . '.' . $IDENTIFIER;
		$debugfile = __CLASS__ . '.' . __FUNCTION__ . '.' . $IDENTIFIER . '-debug.txt';

		# UA --- HEAD $URIR; Accept-Datetime: T ----> URI-R
		# UA <--- 200; Link: URI-G ---- URI-R
		$curlCmd = "curl -v -s -A '$uagent' -b '$sessionCookieString' -k -i -H 'Accept-Datetime: $ACCEPTDATETIME' -H \"X-TestComment: $COMMENT\" --url \"$URIR\"";
		$response = `$curlCmd 2> $debugfile | tee "$outputfile"`;

		$headers = extractHeadersFromResponse($response);
		$statusline = extractStatuslineFromResponse($response);
		$entity = extractEntityFromResponse($response);

		$this->assertEquals("200", $statusline["code"], "200");

		$this->assertArrayHasKey('Link', $headers);
		$this->assertArrayHasKey('Memento-Datetime', $headers);
		$this->assertArrayHasKey('Vary', $headers);
		$this->assertArrayHasKey('Content-Location', $headers);

		$this->assertEquals($URIM, $headers['Content-Location']);

		$relations = extractItemsFromLink($headers['Link']);

		$this->assertArrayHasKey('original latest-version timegate', $relations);
		$this->assertEquals($URIR, $relations['original latest-version timegate']['url']);

		$this->assertArrayHasKey('timemap', $relations, "timemap relation not present in Link Header");
		$this->assertEquals($URIT, $relations['timemap']);

		$varyItems = extractItemsFromVary($headers['Vary']);

		$this->assertContains('Accept-Datetime', $varyItems);

		$this->StandardEntityTests($entity);

		return $response;
	}

    public function DirectOriginalResourceResponseCommonTests(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {
		
		global $sessionCookieString;

		$uagent = "Memento-Mediawiki-Plugin/Test";
		$outputfile = __CLASS__ . '.' . __FUNCTION__ . '.' . $IDENTIFIER . '.txt';
		$debugfile = __CLASS__ . '.' . __FUNCTION__ . '.' . $IDENTIFIER . '-debug.txt';

		$curlCmd = "curl -v -s -A '$uagent' -b '$sessionCookieString' -k -i -H \"X-TestComment: $COMMENT\" --url \"$URIM\"";
		#echo '[' . $curlCmd . "]\n";
		$response = `$curlCmd 2> $debugfile | tee -a "$outputfile"`;
		file_put_contents( $outputfile, "\n#########################################\n", FILE_APPEND );
		file_put_contents( $debugfile, "\n#########################################\n", FILE_APPEND );

		$headers = extractHeadersFromResponse($response);
		$statusline = extractStatuslineFromResponse($response);
		$entity = extractEntityFromResponse($response);

		$this->assertEquals("200", $statusline["code"]);

		$this->assertArrayHasKey('Link', $headers, "No Link Header present");
		$this->assertArrayHasKey('Vary', $headers, "No Vary Header present");

		$varyItems = extractItemsFromVary($headers['Vary']);
		$this->assertContains('Accept-Datetime', $varyItems, "Accept-Datetime not present in Vary Header");

		$relations = extractItemsFromLink($headers['Link']);

		$this->assertArrayHasKey('original latest-version timegate', $relations, "original latest-version timegate relation not present in Link Header");
		$this->assertEquals($URIG, $relations['original latest-version timegate']);

		$this->assertArrayHasKey('timemap', $relations, "timemap relation not present in Link Header");
		$this->assertEquals($URIT, $relations['timemap']);

		$this->StandardEntityTests($entity);

		return $response;
	}


    public function DirectMementoResponseCommonTests(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {
		
		global $sessionCookieString;

		$uagent = "Memento-Mediawiki-Plugin/Test";
		$outputfile = __CLASS__ . '.' . __FUNCTION__ . '.' . $IDENTIFIER . '.txt';
		$debugfile = __CLASS__ . '.' . __FUNCTION__ . '.' . $IDENTIFIER . '-debug.txt';

		$curlCmd = "curl -v -s -A '$uagent' -b '$sessionCookieString' -k -i -H \"X-TestComment: $COMMENT\" --url \"$URIM\"";
		#echo '[' . $curlCmd . "]\n";
		$response = `$curlCmd 2> $debugfile | tee -a "$outputfile"`;
		file_put_contents( $outputfile, "\n#########################################\n", FILE_APPEND );
		file_put_contents( $debugfile, "\n#########################################\n", FILE_APPEND );

		$headers = extractHeadersFromResponse($response);
		$statusline = extractStatuslineFromResponse($response);
		$entity = extractEntityFromResponse($response);

		$this->assertEquals("200", $statusline["code"]);

		$this->assertArrayHasKey('Link', $headers, "No Link Header present");
		$this->assertArrayHasKey('Memento-Datetime', $headers, "No Memento-Datetime Header present");

		$relations = extractItemsFromLink($headers['Link']);
        $mementoDatetime = $headers['Memento-Datetime'];

		$this->assertArrayHasKey('original latest-version timegate', $relations, "original latest-version timegate relation not present in Link Header");
		$this->assertEquals($URIG, $relations['original latest-version timegate']);

		$this->assertArrayHasKey('timemap', $relations, "timemap relation not present in Link Header");
		$this->assertEquals($URIG, $relations['timemap']);

        # need test for expected Memento Datetime, and need data field in input for it too

		$this->StandardEntityTests($entity);

		return $response;
	}

	public function recommendedRelationsTests(
		$response, $FIRSTMEMENTO, $LASTMEMENTO, $URIM ) {

		$headers = extractHeadersFromResponse($response);
		$relations = extractItemsFromLink($headers['Link']);

		if ( ($FIRSTMEMENTO == $LASTMEMENTO) ) {
			$this->assertArrayHasKey('first last memento', $relations);
			$this->assertNotNull($relations['first last memento']['datetime']);
			$this->assertEquals($URIM, $relations['first last memento']['url']);
		} else {
			$this->assertArrayHasKey('first memento', $relations, "'first memento' relation not present in Link field:\n" . extractHeadersStringFromResponse($response) );
			$this->assertNotNull($relations['first memento']['datetime'], "'first memento' relation does not contain a datetime field\n" . extractHeadersStringFromResponse($response) );

			$this->assertArrayHasKey('last memento', $relations, "'last memento' relation not present in Link field");
			$this->assertNotNull($relations['last memento']['datetime'], "'last memento' relation does not contain a datetime field\n" . extractHeadersStringFromResponse($response) );
			$this->assertEquals($FIRSTMEMENTO,
				$relations['first memento']['url'],
		    	"first memento url is not correct\n" . extractHeadersStringFromResponse($response) );

			$this->assertEquals($LASTMEMENTO,
				$relations['last memento']['url'],
				"last memento url is not correct\n" . extractHeadersStringFromResponse($response) );
		}
	}

    /**
	 * @group all
	 *
     * @dataProvider acquireTimeNegotiationData
     */
	public function testDirectMementoResponse(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {

        $response = $this->DirectMementoResponseCommonTests(
			$IDENTIFIER, $ACCEPTDATETIME, $URIR, $ORIGINALLATEST, $FIRSTMEMENTO,
			$LASTMEMENTO, $PREVPREDECESSOR, $NEXTSUCCESSOR,
			$URIM, $URIG, $URIT, $COMMENT );

	}

    /**
	 * @group all
	 *
     * @dataProvider acquireTimeNegotiationData
     */
	public function testDirectMementoResponseWithRecommendedHeaders(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {

        $response = $this->DirectMementoResponseCommonTests(
			$IDENTIFIER, $ACCEPTDATETIME, $URIR, $ORIGINALLATEST, $FIRSTMEMENTO,
			$LASTMEMENTO, $PREVPREDECESSOR, $NEXTSUCCESSOR,
			$URIM, $URIG, $URIT, $COMMENT );

		$this->recommendedRelationsTests( $response, $FIRSTMEMENTO, $LASTMEMENTO );
	}

	/**
	 * @group all
	 *
	 * @dataProvider acquireTimeNegotiationData
	 */
	public function testDirectOriginalResourceResponse(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {
        $response = $this->DirectOriginalResourceResponseCommonTests(
			$IDENTIFIER, $ACCEPTDATETIME, $URIR, $ORIGINALLATEST, $FIRSTMEMENTO,
			$LASTMEMENTO, $PREVPREDECESSOR, $NEXTSUCCESSOR,
			$URIM, $URIG, $URIT, $COMMENT );

	}

	/**
	 * @group all
	 *
	 * @dataProvider acquireTimeNegotiationData
	 */
	public function testDirectOriginalResourceResponseWithRecommendedHeaders(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {
        $response = $this->DirectOriginalResourceResponseCommonTests(
			$IDENTIFIER, $ACCEPTDATETIME, $URIR, $ORIGINALLATEST, $FIRSTMEMENTO,
			$LASTMEMENTO, $PREVPREDECESSOR, $NEXTSUCCESSOR,
			$URIM, $URIG, $URIT, $COMMENT );

		$this->recommendedRelationsTests( $response, $FIRSTMEMENTO, $LASTMEMENTO );
	}

    /**
	 * @group 302-style
	 *
     * @dataProvider acquireTimeNegotiationData
     */
    public function test302StyleTimeGateResponse(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {

        $this->302StyleTimeGateResponseCommonTests( $IDENTIFIER, $ACCEPTDATETIME,
		    $URIR, $ORIGINALLATEST, $FIRSTMEMENTO, $LASTMEMENTO, $PREVPREDECESSOR,
		    $NEXTSUCCESSOR, $URIM, $URIG, $URIT, $COMMENT );
    }

    /**
	 * @group 302-style-recommended-headers
	 *
     * @dataProvider acquireTimeNegotiationData
     */
    public function test302StyleTimeGateResponseWithRecommendedHeaders(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
		) {

        $response = $this->302StyleTimeGateResponseCommonTests( $IDENTIFIER, $ACCEPTDATETIME,
		    $URIR, $ORIGINALLATEST, $FIRSTMEMENTO, $LASTMEMENTO, $PREVPREDECESSOR,
		    $NEXTSUCCESSOR, $URIM, $URIG, $URIT, $COMMENT );

		$this->recommendedRelationsTests( $response, $FIRSTMEMENTO, $LASTMEMENTO );
    }


	/**
	 * @group 200-style
	 *
	 * @dataProvider acquireTimeNegotiationData
     */
    public function test200StyleTimeGateMementoResponse(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
			) {

        $response = $this->200StyleTimeGateResponseCommonTests( $IDENTIFIER, $ACCEPTDATETIME,
		    $URIR, $ORIGINALLATEST, $FIRSTMEMENTO, $LASTMEMENTO, $PREVPREDECESSOR,
		    $NEXTSUCCESSOR, $URIM, $URIG, $URIT, $COMMENT );
	}

	/**
	 * @group 200-style-recommended-headers
	 *
	 * @dataProvider acquireTimeNegotiationData
     */
    public function test200StyleTimeGateMementoResponseWithRecommendedHeaders(
			$IDENTIFIER,
		    $ACCEPTDATETIME,
		    $URIR,
			$ORIGINALLATEST,
		    $FIRSTMEMENTO,
		    $LASTMEMENTO,
			$PREVPREDECESSOR,
		    $NEXTSUCCESSOR,
		    $URIM,
			$URIG,
			$URIT,
			$COMMENT
			) {

        $response = $this->200StyleTimeGateResponseCommonTests( $IDENTIFIER, $ACCEPTDATETIME,
		    $URIR, $ORIGINALLATEST, $FIRSTMEMENTO, $LASTMEMENTO, $PREVPREDECESSOR,
		    $NEXTSUCCESSOR, $URIM, $URIG, $URIT, $COMMENT );

		$this->recommendedRelationsTests( $response, $FIRSTMEMENTO, $LASTMEMENTO );
	}

	/**
	 * @group all
	 *
	 * @dataProvider acquireEditUrls
	 */
	public function testEditPage($URIR) {
		global $sessionCookieString;

		$uagent = "Memento-Mediawiki-Plugin/Test";

		$outputfile = __CLASS__ . '.' . __FUNCTION__ . '.' . self::$instance . '.txt';
		$debugfile = __CLASS__ . '.' . __FUNCTION__ . '-debug-' . self::$instance . '.txt';

		$curlCmd = "curl -v -s -A '$uagent' -b '$sessionCookieString' -k -i --url \"$URIR\"";
		$response = `$curlCmd 2> $debugfile | tee "$outputfile"`;

		$statusline = extractStatusLineFromResponse($response);
		$entity = extractEntityFromResponse($response);

		$this->assertEquals("200", $statusline["code"]);

		$this->StandardEntityTests($entity);
	}
	/**
	 * @group all
	 *
	 * @dataProvider acquireDiffUrls()
	 */
	public function testDiffPage($URIR) {
		global $sessionCookieString;

		$uagent = "Memento-Mediawiki-Plugin/Test";

		$outputfile = __CLASS__ . '.' . __FUNCTION__ . '.' . self::$instance . '.txt';
		$debugfile = __CLASS__ . '.' . __FUNCTION__ . '-debug-' . self::$instance . '.txt';

		# UA <--- 200; Link: URI-G ---- URI-R
		$curlCmd = "curl -v -s -A '$uagent' -b '$sessionCookieString' -k -i --url \"$URIR\"";
		$response = `$curlCmd 2> $debugfile | tee "$outputfile"`;

		$headers = extractHeadersFromResponse($response);
		$statusline = extractStatuslineFromResponse($response);
		$entity = extractEntityFromResponse($response);

		$this->assertEquals($statusline["code"], "200");

		$this->StandardEntityTests($entity);
	}


    public function acquireTimeNegotiationData() {
		return acquireCSVDataFromFile(
			getenv('TESTDATADIR') . '/time-negotiation-testdata.csv', 12);
    }

	# TODO: need an automated test for timemaps' happy path

	public function acquireEditUrls() {
		return acquireLinesFromFile(
			getenv('TESTDATADIR') . '/memento-editpage-testdata.csv');
	}

	public function acquireDiffUrls() {
		return acquireLinesFromFile(
			getenv('TESTDATADIR') . '/memento-diffpage-testdata.csv');
	}

}
