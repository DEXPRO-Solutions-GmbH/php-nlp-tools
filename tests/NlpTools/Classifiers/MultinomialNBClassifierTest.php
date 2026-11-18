<?php

declare(strict_types=1);

namespace NlpTools\Classifiers;

use NlpTools\Documents\TokensDocument;
use NlpTools\Documents\TrainingSet;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Models\FeatureBasedNB;
use PHPUnit\Framework\TestCase;

/**
 * PHP turns numeric strings into int array keys. Class names and tokens such as `7` or `2024` therefore
 * reach the model as int, which must neither raise a TypeError nor change the result.
 */
class MultinomialNBClassifierTest extends TestCase
{
    public function testNumericClassNamesAndNumericTokens(): void
    {
        $trainingSet = new TrainingSet();
        $trainingSet->addDocument('7', new TokensDocument(['rechnung', 'betrag', '2024', '123']));
        $trainingSet->addDocument('7', new TokensDocument(['rechnung', 'mwst', '19']));
        $trainingSet->addDocument('12', new TokensDocument(['lieferschein', 'menge', '4711']));
        $trainingSet->addDocument('12', new TokensDocument(['lieferschein', '0815', '4711']));

        $classifier = $this->classifier($trainingSet);

        $this->assertSame(7, $classifier->classify($trainingSet->getClassSet(), new TokensDocument(['rechnung', '2024', '2024'])));
        $this->assertSame(12, $classifier->classify($trainingSet->getClassSet(), new TokensDocument(['4711', '4711', 'menge'])));
        // the caller decides about the type of the class names; it is returned as given
        $this->assertSame('7', $classifier->classify(['7', '12'], new TokensDocument(['rechnung'])));
        $this->assertSame(12, $classifier->classify([7, 12], new TokensDocument(['lieferschein'])));
    }

    public function testTextualClassNames(): void
    {
        $trainingSet = new TrainingSet();
        $trainingSet->addDocument('invoice', new TokensDocument(['invoice', 'amount', 'total']));
        $trainingSet->addDocument('letter', new TokensDocument(['dear', 'sir', 'regards']));

        $classifier = $this->classifier($trainingSet);

        $this->assertSame('invoice', $classifier->classify($trainingSet->getClassSet(), new TokensDocument(['amount', 'total'])));
        $this->assertSame('letter', $classifier->classify($trainingSet->getClassSet(), new TokensDocument(['dear', 'regards'])));
    }

    public function testWithoutAnyClassTheResultIsEmpty(): void
    {
        $classifier = $this->classifier(new TrainingSet());

        $this->assertSame('', $classifier->classify([], new TokensDocument(['rechnung'])));
    }

    public function testUntrainedModelStillAnswers(): void
    {
        $classifier = $this->classifier(new TrainingSet());

        // nothing is known about any class: every score is the same, so the first class wins
        $this->assertSame('1', $classifier->classify(['1', '2'], new TokensDocument(['a'])));
    }

    private function classifier(TrainingSet $trainingSet): MultinomialNBClassifier
    {
        $dataAsFeatures = new DataAsFeatures();
        $featureBasedNB = new FeatureBasedNB();
        $featureBasedNB->train($dataAsFeatures, $trainingSet);

        return new MultinomialNBClassifier($dataAsFeatures, $featureBasedNB);
    }
}
