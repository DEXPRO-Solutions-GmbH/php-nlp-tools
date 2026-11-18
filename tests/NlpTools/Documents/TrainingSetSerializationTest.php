<?php

declare(strict_types=1);

namespace NlpTools\Documents;

use NlpTools\Classifiers\MultinomialNBClassifier;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Models\FeatureBasedNB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Applications persist TrainingSet objects with serialize(). Files written by the original, untyped
 * library have to load with this one and must re-serialize to the very same bytes.
 */
class TrainingSetSerializationTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function fixtureProvider(): array
    {
        return [
            'numeric class names' => ['numeric.training'],
            'text class names' => ['text.training'],
            'empty set' => ['empty.training'],
            'iterated set' => ['iterated.training'],
        ];
    }

    #[DataProvider('fixtureProvider')]
    public function testFilesOfTheOriginalLibraryRoundTripByteForByte(string $fixture): void
    {
        $bytes = $this->read($fixture);

        $set = unserialize($bytes);

        $this->assertInstanceOf(TrainingSet::class, $set);
        $this->assertSame($bytes, serialize($set));
    }

    public function testNumericFixtureContent(): void
    {
        $set = unserialize($this->read('numeric.training'));
        $this->assertInstanceOf(TrainingSet::class, $set);

        $this->assertCount(5, $set);
        $this->assertSame([7, 12, 105], $set->getClassSet());

        $classes = [];
        foreach ($set as $document) {
            $classes[] = $document->getClass();
        }

        $this->assertSame(['7', '7', '12', '12', '105'], $classes);
    }

    public function testFreshSetSerializesLikeTheOriginalLibrary(): void
    {
        $this->assertSame($this->read('empty.training'), serialize(new TrainingSet()));

        $trainingSet = new TrainingSet();
        $trainingSet->addDocument('7', new TokensDocument(['rechnung', 'betrag', '2024', '123', 'summe']));
        $trainingSet->addDocument('7', new TokensDocument(['rechnung', 'mwst', '19', 'netto']));
        $trainingSet->addDocument('12', new TokensDocument(['lieferschein', 'menge', 'artikel', '4711']));
        $trainingSet->addDocument('12', new TokensDocument(['lieferschein', '0815', 'artikel']));
        $trainingSet->addDocument('105', new TokensDocument(['vertrag', 'kuendigung', '2024', 'datum']));

        $this->assertSame($this->read('numeric.training'), serialize($trainingSet));
    }

    public function testLoadedSetCanBeIteratedAndTrained(): void
    {
        $set = unserialize($this->read('iterated.training'));
        $this->assertInstanceOf(TrainingSet::class, $set);

        $seen = 0;
        foreach ($set as $document) {
            ++$seen;
            $this->assertSame(['a', 'b'], $document->getDocumentData());
        }

        $this->assertSame(1, $seen);
    }

    public function testClassifiesWithALoadedSet(): void
    {
        $set = unserialize($this->read('numeric.training'));
        $this->assertInstanceOf(TrainingSet::class, $set);

        $dataAsFeatures = new DataAsFeatures();
        $featureBasedNB = new FeatureBasedNB();
        $featureBasedNB->train($dataAsFeatures, $set);

        $multinomialNBClassifier = new MultinomialNBClassifier($dataAsFeatures, $featureBasedNB);

        $this->assertSame(7, $multinomialNBClassifier->classify($set->getClassSet(), new TokensDocument(['rechnung', 'betrag', '2024'])));
        $this->assertSame(12, $multinomialNBClassifier->classify($set->getClassSet(), new TokensDocument(['lieferschein', 'artikel', '4711'])));
    }

    private function read(string $fixture): string
    {
        $bytes = file_get_contents(TEST_DATA_DIR . '/Serialized/' . $fixture);
        $this->assertIsString($bytes);

        return $bytes;
    }
}
