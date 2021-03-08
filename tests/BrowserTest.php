<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\{RemoteWebDriver, DesiredCapabilities};
use Facebook\WebDriver\WebDriverBy;
use FourPointTwo\Database\Database;
use FourPointTwo\Models\Pokemon;

final class BrowserTest extends TestCase
{
	private static string $baseUri;
	private static RemoteWebDriver $driver;
	private static array $pokemon;

	public static function setUpBeforeClass(): void
	{
		self::$baseUri = "http://apache/Exercises/4.2-Pokemon-Views/public";
		self::$driver = RemoteWebDriver::create("http://firefox:4444/wd/hub", DesiredCapabilities::firefox());
	}

	public function setUp(): void
	{
		self::$pokemon = [
			['name' => 'Bulbasaur', 'type' => 'Grass'],
			['name' => 'Charmander', 'type' => 'Fire'],
			['name' => 'Squirtle', 'type' => 'Water'],
			['name' => 'Pikachu', 'type' => 'Lightning'],
			['name' => 'Pidgeotto', 'type' => 'Flying'],
			['name' => 'Koffing', 'type' => 'Poison'],
			['name' => 'Dragonite', 'type' => 'Dragon'],
			['name' => 'Machamp', 'type' => 'Fighting'],
			['name' => 'Clefairy', 'type' => 'Fairy'],
			['name' => 'Eevee', 'type' => 'Normal'],
			['name' => 'Sandslash', 'type' => 'Ground'],
			['name' => 'Vulpix', 'type' => 'Fire'],
			['name' => 'Alakazam', 'type' => 'Psychic'],
			['name' => 'Onyx', 'type' => 'Rock'],
			['name' => 'Hitmonlee', 'type' => 'Fighting'],
			['name' => 'Snorlax', 'type' => 'Normal']
		];
	}

	public function testHome(): void
	{
		self::$driver->get(self::$baseUri);

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$nav = self::$driver->findElement(WebDriverBy::cssSelector('header > nav'));
		$footer = self::$driver->findElement(WebDriverBy::cssSelector('footer'));

		$this->assertStringContainsString('Homepage!', $h1->getText());
		$this->assertStringContainsString('Home', $nav->getText());
		$this->assertStringContainsString('Create', $nav->getText());
		$this->assertStringContainsString('List All Pokemon', $nav->getText());
		$this->assertStringContainsString('© Copyright 2020 Vikram Singh', $footer->getText());
	}

	public function testInvalidEndpoint(): void
	{
		self::$driver->get(self::$baseUri . '/digimon');

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Error', $h1->getText());
		$this->assertStringContainsString('404', $body->getText());
	}

	public function testPokemonWasCreatedSuccessfully(): void
	{
		$pokemonData = $this->generateRandomPokemon();

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.create'))->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$nameTypeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#name'));
		$typeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#type'));
		$submitButton = self::$driver->findElement(WebDriverBy::cssSelector('form > input#submit'));

		$this->assertEquals('Register Pokemon', $h1->getText());

		$nameTypeInput->sendKeys($pokemonData['name']);
		$typeInput->sendKeys($pokemonData['type']);
		$submitButton->click();

		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Success!', $body->getText());
		$this->assertStringContainsString($pokemonData['name'], $body->getText());
		$this->assertStringContainsString($pokemonData['type'], $body->getText());
	}

	public function testPokemonWasNotCreatedWithBlankName(): void
	{
		$randomPokemon = $this->generateRandomPokemon();

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.create'))->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$typeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#type'));
		$submitButton = self::$driver->findElement(WebDriverBy::cssSelector('form > input#submit'));

		$typeInput->sendKeys($randomPokemon['type']);
		$submitButton->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Error', $h1->getText());
		$this->assertStringContainsString('Cannot create Pokemon: Missing name.', $body->getText());
	}

	public function testPokemonWasNotCreatedWithBlankType(): void
	{
		$randomPokemon = $this->generateRandomPokemon();

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.create'))->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$nameType = self::$driver->findElement(WebDriverBy::cssSelector('form > input#name'));
		$submitButton = self::$driver->findElement(WebDriverBy::cssSelector('form > input#submit'));

		$nameType->sendKeys($randomPokemon['name']);
		$submitButton->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Error', $h1->getText());
		$this->assertStringContainsString('Cannot create Pokemon: Missing type.', $body->getText());
	}

	public function testPokemonWasNotCreatedWithDuplicateName(): void
	{
		$pokemon = Pokemon::create($this->generateRandomPokemon());

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.create'))->click();

		$nameTypeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#name'));
		$typeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#type'));
		$submitButton = self::$driver->findElement(WebDriverBy::cssSelector('form > input#submit'));

		$nameTypeInput->sendKeys($pokemon->getName());
		$typeInput->sendKeys($pokemon->getType());
		$submitButton->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Error', $h1->getText());
		$this->assertStringContainsString('Cannot create Pokemon: Pokemon already exists.', $body->getText());
	}

	public function testAllPokemonWereRetrievedSuccessfully(): void
	{
		for ($i = 0; $i < rand(1, 10); $i++) {
			$pokemon[] = Pokemon::create($this->generateRandomPokemon());
		}

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.list'))->click();

		$pokemonTable = self::$driver->findElements(WebDriverBy::cssSelector('tbody > tr'));

		$this->assertEquals(sizeOf($pokemon), sizeOf($pokemonTable));

		for ($i = 0; $i < sizeOf($pokemon); $i++) {
			$this->assertStringContainsString($pokemon[$i]->getName(), $pokemonTable[$i]->getText());
			$this->assertStringContainsString($pokemon[$i]->getType(), $pokemonTable[$i]->getText());
		}
	}

	public function testAllPokemonWereNotRetrieved(): void
	{
		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.list'))->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Error', $h1->getText());
		$this->assertStringContainsString('Cannot find Pokemon: No Pokemon exist in the database.', $body->getText());
	}

	public function testPokemonWasFoundById(): void
	{
		$pokemon = Pokemon::create($this->generateRandomPokemon());

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.list'))->click();

		$tableHeader = self::$driver->findElement(WebDriverBy::cssSelector('table > thead'));

		$this->assertStringContainsString('Name', $tableHeader->getText());
		$this->assertStringContainsString('Type', $tableHeader->getText());
		$this->assertStringContainsString('Edit', $tableHeader->getText());
		$this->assertStringContainsString('Delete', $tableHeader->getText());

		self::$driver->findElement(WebDriverBy::cssSelector('table > tbody > tr > td > a.' . $pokemon->getName()))->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$h2 = self::$driver->findElement(WebDriverBy::cssSelector('h2'));

		$this->assertStringContainsString($pokemon->getName(), $h1->getText());
		$this->assertStringContainsString($pokemon->getType(), $h2->getText());
	}

	public function testPokemonWasNotFoundByWrongId(): void
	{
		$randomPokemonId = rand(1, 100);
		self::$driver->get(self::$baseUri . "/pokemon/$randomPokemonId");

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Error', $h1->getText());
		$this->assertStringContainsString("Cannot find Pokemon: No Pokemon exists with ID #$randomPokemonId.", $body->getText());
	}

	public function testPokemonWasUpdated(): void
	{
		$pokemon = Pokemon::create($this->generateRandomPokemon());
		$newPokemonData = $this->generateRandomPokemon();

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.list'))->click();
		$editButton = self::$driver->findElements(WebDriverBy::cssSelector('form > input.edit-button'));
		$editButton[0]->click();

		$nameTypeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#name'));
		$typeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#type'));
		$submitButton = self::$driver->findElement(WebDriverBy::cssSelector('form > input#submit'));

		$nameTypeInput->sendKeys($newPokemonData['name']);
		$typeInput->sendKeys($newPokemonData['type']);
		$submitButton->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Success!', $h1->getText());
		$this->assertStringContainsString('Pokemon was edited successfully!', $body->getText());
		$this->assertStringContainsString($pokemon->getId(), $body->getText());
		$this->assertStringContainsString($newPokemonData['name'], $body->getText());
		$this->assertStringContainsString($newPokemonData['type'], $body->getText());
	}

	public function testPokemonWasNotUpdatedWithMissingName(): void
	{
		Pokemon::create($this->generateRandomPokemon());
		$newPokemonData = $this->generateRandomPokemon();

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.list'))->click();
		$editButton = self::$driver->findElements(WebDriverBy::cssSelector('form > input.edit-button'));
		$editButton[0]->click();

		$typeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#type'));
		$submitButton = self::$driver->findElement(WebDriverBy::cssSelector('form > input#submit'));

		$typeInput->sendKeys($newPokemonData['type']);
		$submitButton->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Error', $h1->getText());
		$this->assertStringContainsString("Cannot update Pokemon: Missing name.", $body->getText());
	}

	public function testPokemonWasNotUpdatedWithMissingType(): void
	{
		Pokemon::create($this->generateRandomPokemon());
		$newPokemonData = $this->generateRandomPokemon();

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.list'))->click();
		$editButton = self::$driver->findElements(WebDriverBy::cssSelector('form > input.edit-button'));
		$editButton[0]->click();

		$nameTypeInput = self::$driver->findElement(WebDriverBy::cssSelector('form > input#name'));
		$submitButton = self::$driver->findElement(WebDriverBy::cssSelector('form > input#submit'));

		$nameTypeInput->sendKeys($newPokemonData['name']);
		$submitButton->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Error', $h1->getText());
		$this->assertStringContainsString("Cannot update Pokemon: Missing type.", $body->getText());
	}

	public function testPokemonWasDeletedSuccessfully(): void
	{
		$pokemon = Pokemon::create($this->generateRandomPokemon());

		self::$driver->get(self::$baseUri);
		self::$driver->findElement(WebDriverBy::cssSelector('nav > ul > li > a.list'))->click();
		$deleteButton = self::$driver->findElements(WebDriverBy::cssSelector('form > input.delete-button'));
		$deleteButton[0]->click();

		$h1 = self::$driver->findElement(WebDriverBy::cssSelector('h1'));
		$body = self::$driver->findElement(WebDriverBy::cssSelector('body'));

		$this->assertStringContainsString('Success!', $h1->getText());
		$this->assertStringContainsString('Pokemon was deleted successfully!', $body->getText());
		$this->assertStringContainsString($pokemon->getId(), $body->getText());
		$this->assertStringContainsString($pokemon->getName(), $body->getText());
		$this->assertStringContainsString($pokemon->getType(), $body->getText());
	}

	/**
	 * Since a Pokemon can only be added to the DB once, we have to splice from the array.
	 *
	 * @return array
	 */
	private function generateRandomPokemon(): array
	{
		return array_splice(self::$pokemon, rand(0, sizeOf(self::$pokemon) - 1), 1)[0];
	}

	public function tearDown(): void
	{
		Database::getInstance()->truncate('pokemon');
	}

	public static function tearDownAfterClass(): void
	{
		self::$driver->close();
	}
}
