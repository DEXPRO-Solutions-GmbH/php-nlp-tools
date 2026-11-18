<?php

declare(strict_types=1);

namespace NlpTools\Documents;

use NlpTools\Utils\TransformationInterface;
use NlpTools\Documents\DocumentInterface;

/**
 * A TrainingDocument is a document that "decorates" any other document
 * to add the real class of the document. It is used while training
 * together with the training set.
 */
class TrainingDocument implements DocumentInterface
{
    /**
     * The decorated document.
     *
     * The property names and their declaration order are part of the serialization format of
     * TrainingSet files that applications keep on disk (see TrainingSetSerializationTest); they have to stay
     * `d` and `class`.
     */
    protected DocumentInterface $d;

    /**
     * The actual class. Numeric class names are turned into int array keys by PHP, so both
     * types occur in practice and are kept as given.
     */
    protected int|string $class;

    /**
     * @param int|string        $class The actual class of the Document $d
     * @param DocumentInterface $document The document to be decorated
     */
    public function __construct(int|string $class, DocumentInterface $document)
    {
        $this->d = $document;
        $this->class = $class;
    }

    /**
     * @return array<int, string>
     */
    public function getDocumentData(): array
    {
        return $this->d->getDocumentData();
    }

    public function getClass(): int|string
    {
        return $this->class;
    }

    /**
     * Pass the transformation to the decorated document
     */
    public function applyTransformation(TransformationInterface $transformation): void
    {
        $this->d->applyTransformation($transformation);
    }
}
