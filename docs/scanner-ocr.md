# Scanner, OCR e exportação PDF

O Scanner captura imagens reais pela câmera do navegador (`getUserMedia`) ou por seleção de arquivos e persiste cada página em `storage/scanner`, junto de `digitalizacoes`, `paginas_digitalizacao` e uma entrada pendente em `conteudos_ocr`.

OCR não é executado pelo PHP puro. Para processar as entradas pendentes, instale o [Tesseract OCR](https://tesseract-ocr.github.io/tessdoc/Installation.html) no servidor, com o pacote de idioma desejado, por exemplo `por`. Um worker deve chamar o binário `tesseract` e gravar o texto retornado em `texto_extraido` e a confiança em `precisao`.

Para exportar páginas como PDF, instale uma biblioteca apropriada, por exemplo `composer require mpdf/mpdf`. A geração não é simulada enquanto essa dependência não estiver integrada.
