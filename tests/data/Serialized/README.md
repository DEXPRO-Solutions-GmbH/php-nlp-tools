# Serialized training sets

These `*.training` files were written with `serialize()` by the original, untyped `NlpTools` 1.0.x-dev
(angeloskath/php-nlp-tools, commit `476e07c`) on PHP 8.3. They pin the serialization format of
`TrainingSet`, `TrainingDocument` and `TokensDocument`: applications keep such files on disk, so they have
to stay loadable and re-serialize byte for byte. Do not regenerate them with this fork.

* `numeric.training`  – numeric class names (`7`, `12`, `105`) and numeric tokens
* `text.training`     – textual class names
* `empty.training`    – an empty `TrainingSet`
* `iterated.training` – a set that had been iterated before it was serialized (`currentDocument` is set)
