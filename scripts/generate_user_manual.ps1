param(
    [string]$SourcePath = "C:\xampp\htdocs\AlesturTesting\docs\manual_usuario_alestur.md",
    [string]$OutputDocx = "C:\xampp\htdocs\AlesturTesting\docs\Manual_Usuario_Over_Alestur.docx",
    [string]$OutputPdf = "C:\xampp\htdocs\AlesturTesting\docs\Manual_Usuario_Over_Alestur.pdf",
    [string]$LogoPath = "C:\xampp\htdocs\AlesturTesting\public\img\home\logo.png"
)

$ErrorActionPreference = "Stop"

function Add-CoverPage {
    param($Selection, [string]$LogoPath)

    if (Test-Path $LogoPath) {
        $Selection.InlineShapes.AddPicture($LogoPath) | Out-Null
        $Selection.TypeParagraph()
    }

    $Selection.Style = "Title"
    $Selection.Font.Name = "Calibri"
    $Selection.Font.Size = 26
    $Selection.Font.Color = 0
    $Selection.TypeText("Manual de Usuario")
    $Selection.TypeParagraph()

    $Selection.Style = "Subtitle"
    $Selection.Font.Name = "Calibri"
    $Selection.Font.Size = 16
    $Selection.Font.Color = 8421504
    $Selection.TypeText("Proyecto Over Alestur")
    $Selection.TypeParagraph()
    $Selection.TypeParagraph()

    $Selection.Style = "Normal"
    $Selection.Font.Name = "Calibri"
    $Selection.Font.Size = 11
    $Selection.Font.Color = 0
    $Selection.TypeText("Documento operativo del sistema web, CRM comercial y panel administrativo.")
    $Selection.TypeParagraph()
    $Selection.TypeText("Fecha de emision: 19 de mayo de 2026")
    $Selection.TypeParagraph()
    $Selection.TypeText("Version del manual: 1.0")
    $Selection.InsertBreak(7)
}

function Add-Toc {
    param($Document, $Selection)

    $Selection.Style = "Heading 1"
    $Selection.TypeText("Tabla de contenido")
    $Selection.TypeParagraph()

    $range = $Selection.Range
    $Document.TablesOfContents.Add($range, $true, 1, 3) | Out-Null
    $Selection.TypeParagraph()
    $Selection.InsertBreak(7)
}

function Apply-ParagraphFormat {
    param($Paragraph, [string]$Kind)

    switch ($Kind) {
        "heading1" {
            $Paragraph.Style = "Heading 1"
            $Paragraph.Range.Font.Name = "Calibri"
            $Paragraph.Range.Font.Size = 16
            $Paragraph.Range.Font.Bold = 1
            $Paragraph.Range.Font.Color = 11673811
            $Paragraph.Format.SpaceBefore = 18
            $Paragraph.Format.SpaceAfter = 10
            $Paragraph.Format.LineSpacingRule = 4
            $Paragraph.Format.LineSpacing = 15
        }
        "heading2" {
            $Paragraph.Style = "Heading 2"
            $Paragraph.Range.Font.Name = "Calibri"
            $Paragraph.Range.Font.Size = 13
            $Paragraph.Range.Font.Bold = 1
            $Paragraph.Range.Font.Color = 11673811
            $Paragraph.Format.SpaceBefore = 14
            $Paragraph.Format.SpaceAfter = 7
            $Paragraph.Format.LineSpacingRule = 4
            $Paragraph.Format.LineSpacing = 15
        }
        "heading3" {
            $Paragraph.Style = "Heading 3"
            $Paragraph.Range.Font.Name = "Calibri"
            $Paragraph.Range.Font.Size = 12
            $Paragraph.Range.Font.Bold = 1
            $Paragraph.Range.Font.Color = 7880991
            $Paragraph.Format.SpaceBefore = 10
            $Paragraph.Format.SpaceAfter = 5
            $Paragraph.Format.LineSpacingRule = 4
            $Paragraph.Format.LineSpacing = 15
        }
        "bullet" {
            $Paragraph.Style = "Normal"
            $Paragraph.Range.Font.Name = "Calibri"
            $Paragraph.Range.Font.Size = 11
            $Paragraph.Format.SpaceBefore = 0
            $Paragraph.Format.SpaceAfter = 4
            $Paragraph.Format.LineSpacingRule = 4
            $Paragraph.Format.LineSpacing = 15
        }
        default {
            $Paragraph.Style = "Normal"
            $Paragraph.Range.Font.Name = "Calibri"
            $Paragraph.Range.Font.Size = 11
            $Paragraph.Range.Font.Color = 0
            $Paragraph.Format.SpaceBefore = 0
            $Paragraph.Format.SpaceAfter = 6
            $Paragraph.Format.LineSpacingRule = 4
            $Paragraph.Format.LineSpacing = 15
        }
    }
}

function Add-MarkdownContent {
    param($Document, $Selection, [string[]]$Lines)

    foreach ($line in $Lines) {
        $trimmed = $line.TrimEnd()

        if ($trimmed -eq "") {
            $Selection.TypeParagraph()
            continue
        }

        if ($trimmed.StartsWith("# ")) {
            continue
        }

        if ($trimmed.StartsWith("## ")) {
            $Selection.Style = "Heading 1"
            $Selection.TypeText($trimmed.Substring(3))
            $Selection.TypeParagraph()
            Apply-ParagraphFormat -Paragraph $Selection.Paragraphs.Last -Kind "heading1"
            continue
        }

        if ($trimmed.StartsWith("### ")) {
            $Selection.Style = "Heading 2"
            $Selection.TypeText($trimmed.Substring(4))
            $Selection.TypeParagraph()
            Apply-ParagraphFormat -Paragraph $Selection.Paragraphs.Last -Kind "heading2"
            continue
        }

        if ($trimmed.StartsWith("- ")) {
            $Selection.Style = "Normal"
            $Selection.Range.ListFormat.ApplyBulletDefault()
            $Selection.TypeText($trimmed.Substring(2))
            $Selection.TypeParagraph()
            Apply-ParagraphFormat -Paragraph $Selection.Paragraphs.Last -Kind "bullet"
            continue
        }

        $Selection.Range.ListFormat.RemoveNumbers()
        $Selection.Style = "Normal"
        $Selection.TypeText($trimmed)
        $Selection.TypeParagraph()
        Apply-ParagraphFormat -Paragraph $Selection.Paragraphs.Last -Kind "normal"
    }
}

function Configure-Document {
    param($Document)

    $section = $Document.Sections.Item(1)
    $section.PageSetup.TopMargin = 72
    $section.PageSetup.BottomMargin = 72
    $section.PageSetup.LeftMargin = 72
    $section.PageSetup.RightMargin = 72
    $section.PageSetup.HeaderDistance = 35
    $section.PageSetup.FooterDistance = 35

    $normal = $Document.Styles.Item("Normal")
    $normal.Font.Name = "Calibri"
    $normal.Font.Size = 11
    $normal.ParagraphFormat.SpaceAfter = 6
    $normal.ParagraphFormat.LineSpacingRule = 4
    $normal.ParagraphFormat.LineSpacing = 15

    $headerRange = $section.Headers.Item(1).Range
    $headerRange.Text = "Manual de Usuario - Proyecto Over Alestur"
    $headerRange.Font.Name = "Calibri"
    $headerRange.Font.Size = 9
    $headerRange.ParagraphFormat.Alignment = 2

    $footerRange = $section.Footers.Item(1).Range
    $footerRange.Text = "Over Alestur"
    $footerRange.Font.Name = "Calibri"
    $footerRange.Font.Size = 9
    $footerRange.ParagraphFormat.Alignment = 1
    $footerRange.Collapse(0)
    $footerRange.Fields.Add($footerRange, -1, "PAGE", $false) | Out-Null
}

$sourceLines = Get-Content -LiteralPath $SourcePath -Encoding UTF8

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

try {
    $document = $word.Documents.Add()
    Configure-Document -Document $document

    $selection = $word.Selection
    Add-CoverPage -Selection $selection -LogoPath $LogoPath
    Add-Toc -Document $document -Selection $selection
    Add-MarkdownContent -Document $document -Selection $selection -Lines $sourceLines

    foreach ($toc in $document.TablesOfContents) {
        $toc.Update() | Out-Null
    }

    $document.SaveAs([ref]$OutputDocx, [ref]16)
    $document.ExportAsFixedFormat($OutputPdf, 17)
    $document.Close()
}
finally {
    $word.Quit()
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
