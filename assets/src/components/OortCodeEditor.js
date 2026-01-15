import Editor from '@monaco-editor/react';

/**
 * Monaco Editor component for Prograde Oort
 * Provides PHP code editing with WordPress function autocomplete
 */
const OortCodeEditor = ({ value, onChange, height = '600px' }) => {
    const handleEditorDidMount = (editor, monaco) => {
        // Configure PHP language defaults
        monaco.languages.typescript.javascriptDefaults.setDiagnosticsOptions({
            noSemanticValidation: true,
            noSyntaxValidation: false
        });

        // Add WordPress function autocomplete
        monaco.languages.registerCompletionItemProvider('php', {
            provideCompletionItems: (model, position) => {
                const suggestions = [
                    // WordPress Core Functions
                    {
                        label: 'wp_insert_post',
                        kind: monaco.languages.CompletionItemKind.Function,
                        insertText: 'wp_insert_post(${1:$args})',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'Insert or update a post'
                    },
                    {
                        label: 'update_post_meta',
                        kind: monaco.languages.CompletionItemKind.Function,
                        insertText: 'update_post_meta(${1:$post_id}, ${2:$meta_key}, ${3:$meta_value})',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'Update post meta field'
                    },
                    {
                        label: 'get_post_meta',
                        kind: monaco.languages.CompletionItemKind.Function,
                        insertText: 'get_post_meta(${1:$post_id}, ${2:$key}, ${3:true})',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'Retrieve post meta field'
                    },
                    // Guzzle HTTP Client
                    {
                        label: 'GuzzleHttp\\Client',
                        kind: monaco.languages.CompletionItemKind.Class,
                        insertText: 'use GuzzleHttp\\\\Client;\n\n$client = new Client();\n$response = $client->${1:post}(${2:$url}, [\n\t\'json\' => ${3:$data}\n]);',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'HTTP client for API requests'
                    },
                    // Action Scheduler
                    {
                        label: 'as_enqueue_async_action',
                        kind: monaco.languages.CompletionItemKind.Function,
                        insertText: 'as_enqueue_async_action(${1:\'hook_name\'}, ${2:$args}, ${3:\'group\'})',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'Schedule an async action via Action Scheduler'
                    },
                    {
                        label: 'as_schedule_single_action',
                        kind: monaco.languages.CompletionItemKind.Function,
                        insertText: 'as_schedule_single_action(${1:time()}, ${2:\'hook_name\'}, ${3:$args})',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'Schedule a one-time action'
                    },
                    // Oort Logger
                    {
                        label: '\\ProgradeOort\\Log\\Logger::instance()->info',
                        kind: monaco.languages.CompletionItemKind.Method,
                        insertText: '\\\\ProgradeOort\\\\Log\\\\Logger::instance()->info(${1:$message}, ${2:$context}, ${3:\'channel\'})',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'Log info message'
                    },
                    // Data transformation
                    {
                        label: 'json_decode',
                        kind: monaco.languages.CompletionItemKind.Function,
                        insertText: 'json_decode(${1:$json}, ${2:true})',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'Decode JSON string'
                    },
                    {
                        label: 'json_encode',
                        kind: monaco.languages.CompletionItemKind.Function,
                        insertText: 'json_encode(${1:$data}, JSON_PRETTY_PRINT)',
                        insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                        documentation: 'Encode data as JSON'
                    }
                ];

                return { suggestions };
            }
        });

        // Set editor options
        editor.updateOptions({
            minimap: { enabled: true },
            fontSize: 14,
            lineNumbers: 'on',
            roundedSelection: false,
            scrollBeyondLastLine: false,
            automaticLayout: true,
            tabSize: 4,
            wordWrap: 'on'
        });
    };

    return (
        <Editor
            height={height}
            defaultLanguage="php"
            value={value}
            onChange={onChange}
            onMount={handleEditorDidMount}
            theme="vs-dark"
            options={{
                selectOnLineNumbers: true,
                matchBrackets: 'always',
                formatOnPaste: true,
                formatOnType: true
            }}
        />
    );
};

export default OortCodeEditor;
