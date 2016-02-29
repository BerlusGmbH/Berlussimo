#LyX 2.0 created this file. For more info see http://www.lyx.org/
\lyxformat 413
\begin_document
\begin_header
\textclass scrbook
\begin_preamble
\end_preamble
\use_default_options true
\maintain_unincluded_children false
\language english
\language_package default
\inputencoding auto
\fontencoding global
\font_roman default
\font_sans default
\font_typewriter default
\font_default_family default
\use_non_tex_fonts false
\font_sc false
\font_osf false
\font_sf_scale 100
\font_tt_scale 100

\graphics default
\default_output_format default
\output_sync 0
\bibtex_command default
\index_command default
\paperfontsize default
\spacing single
\use_hyperref false
\papersize a4paper
\use_geometry false
\use_amsmath 1
\use_esint 1
\use_mhchem 1
\use_mathdots 1
\cite_engine basic
\use_bibtopic false
\use_indices false
\paperorientation portrait
\suppress_date false
\use_refstyle 1
\index Index
\shortcut idx
\color #008000
\end_index
\secnumdepth 3
\tocdepth 3
\paragraph_separation indent
\paragraph_indentation default
\quotes_language english
\papercolumns 1
\papersides 2
\paperpagestyle default
\tracking_changes false
\output_changes false
\html_math_output 0
\html_css_as_file 0
\html_be_strict false
\end_header

\begin_body

@foreach($tables as $name => $table)
\begin_layout Subsection
{!! $name !!}
\end_layout

\begin_layout Subsubsection
Data Definition
\end_layout

\begin_layout Standard
\begin_inset listings
inline false
status open

@foreach(explode("\n", $table['Create Table']) as $line)
\begin_layout Plain Layout

{!! $line !!}

\end_layout
@endforeach

\end_inset
\end_layout

\begin_layout Subsubsection
Attributes
\end_layout

@foreach($table['Columns'] as $column)
\begin_layout Description
{!! $column['Field'] !!} {!! $column['Comment'] !!}
\end_layout
@endforeach

\begin_layout Subsubsection
Usages in Code
\end_layout

@if(isset($table['Usages']))
@foreach($table['Usages'] as $usages)
    \begin_layout Paragraph
    {!! $usages['File'] !!}
    \end_layout
    \begin_layout Standard
    \begin_inset listings
    inline false
    status open

    @foreach(explode("\n", $usages['Lines']) as $line)
        \begin_layout Plain Layout
        {!! trim($line) !!}
        \end_layout
    @endforeach
    \end_inset
    \end_layout
@endforeach
@endif

\end_layout
@endforeach

\end_body
\end_document