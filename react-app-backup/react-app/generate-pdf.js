const { jsPDF } = require('jspdf');

// Create a new PDF document
const doc = new jsPDF();

// Set up styling
const pageWidth = doc.internal.pageSize.width;
const pageHeight = doc.internal.pageSize.height;
const margin = 20;
const maxWidth = pageWidth - 2 * margin;
let yPosition = margin;

// Helper function to add text with word wrap
function addText(text, fontSize = 11, fontStyle = 'normal', color = [0, 0, 0]) {
    doc.setFontSize(fontSize);
    doc.setFont('helvetica', fontStyle);
    doc.setTextColor(...color);

    const lines = doc.splitTextToSize(text, maxWidth);

    lines.forEach(line => {
        if (yPosition > pageHeight - margin) {
            doc.addPage();
            yPosition = margin;
        }
        doc.text(line, margin, yPosition);
        yPosition += fontSize * 0.5;
    });
    yPosition += 5;
}

// Helper function to add heading
function addHeading(text, level = 1) {
    const sizes = { 1: 20, 2: 16, 3: 14 };
    const fontSize = sizes[level] || 12;

    if (yPosition > pageHeight - 40) {
        doc.addPage();
        yPosition = margin;
    }

    addText(text, fontSize, 'bold', [0, 51, 102]);
}

// Helper function to add bullet point
function addBullet(text) {
    if (yPosition > pageHeight - margin) {
        doc.addPage();
        yPosition = margin;
    }

    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(0, 0, 0);

    doc.text('•', margin + 5, yPosition);
    const lines = doc.splitTextToSize(text, maxWidth - 10);

    lines.forEach((line, index) => {
        if (yPosition > pageHeight - margin) {
            doc.addPage();
            yPosition = margin;
        }
        doc.text(line, margin + 15, yPosition);
        yPosition += 5.5;
    });
}

// Helper function to add code block
function addCodeBlock(code) {
    if (yPosition > pageHeight - margin) {
        doc.addPage();
        yPosition = margin;
    }

    doc.setFillColor(245, 245, 245);
    const codeHeight = 15;
    doc.rect(margin, yPosition - 5, maxWidth, codeHeight, 'F');

    doc.setFontSize(10);
    doc.setFont('courier', 'normal');
    doc.setTextColor(51, 51, 51);
    doc.text(code, margin + 5, yPosition + 3);

    yPosition += codeHeight + 5;
    doc.setFont('helvetica', 'normal');
}

// Title Page
doc.setFillColor(0, 51, 102);
doc.rect(0, 0, pageWidth, 80, 'F');

doc.setTextColor(255, 255, 255);
doc.setFontSize(28);
doc.setFont('helvetica', 'bold');
doc.text('Claude Code & Sonnet 4.5', pageWidth / 2, 40, { align: 'center' });

doc.setFontSize(14);
doc.setFont('helvetica', 'normal');
doc.text('Latest Features and Usage Guide', pageWidth / 2, 55, { align: 'center' });

doc.setFontSize(10);
doc.text('October 2025', pageWidth / 2, 70, { align: 'center' });

yPosition = 100;
doc.setTextColor(0, 0, 0);

// Table of Contents
addHeading('Table of Contents', 1);
yPosition += 5;
addText('1. Introduction', 11, 'normal');
addText('2. Claude Sonnet 4.5 Overview', 11, 'normal');
addText('3. Claude Sonnet 4.5 Key Features', 11, 'normal');
addText('4. Claude Code Overview', 11, 'normal');
addText('5. Claude Code Installation', 11, 'normal');
addText('6. Claude Code Key Features', 11, 'normal');
addText('7. How to Use Claude Code', 11, 'normal');
addText('8. Best Practices and Tips', 11, 'normal');

// New Page - Introduction
doc.addPage();
yPosition = margin;

addHeading('1. Introduction', 1);
addText('This guide provides a comprehensive overview of Claude Sonnet 4.5 and Claude Code, including their latest features, capabilities, and practical usage instructions. Released in September 2025, these tools represent significant advances in AI-assisted development.');

yPosition += 5;

// Claude Sonnet 4.5 Overview
addHeading('2. Claude Sonnet 4.5 Overview', 1);
addText('Claude Sonnet 4.5 is Anthropic\'s most intelligent AI model, specifically optimized for coding and building complex agents. It represents a major leap forward in autonomous operation and software development capabilities.');

yPosition += 3;
addText('Key Highlights:', 11, 'bold');
addBullet('State-of-the-art coding performance on SWE-bench Verified');
addBullet('Can run autonomously for up to 30 hours on complex tasks');
addBullet('61.4% success rate on OSWorld (real-world computer tasks)');
addBullet('Same pricing as Sonnet 4: $3/$15 per million tokens');
addBullet('Available on Claude API, Amazon Bedrock, and Google Cloud Vertex AI');

yPosition += 5;

// Claude Sonnet 4.5 Key Features
addHeading('3. Claude Sonnet 4.5 Key Features', 1);

addHeading('3.1 Advanced Coding Capabilities', 2);
addBullet('Best-in-class code generation and analysis');
addBullet('Enhanced system design and planning');
addBullet('Improved security engineering');
addBullet('Excels at autonomous long-horizon coding tasks');
addBullet('Superior performance on real-world software coding challenges');

yPosition += 3;

addHeading('3.2 Extended Autonomous Operation', 2);
addBullet('Can work independently for 30 hours vs. 7 hours for Opus 4');
addBullet('Maintains focus on complex, multi-step tasks throughout extended sessions');
addBullet('Effective planning and execution for projects spanning hours or days');

yPosition += 3;

addHeading('3.3 Agent Building Excellence', 2);
addBullet('Strongest model for building complex, independent agents');
addBullet('Best model at using computers autonomously');
addBullet('Advanced context management across sessions');
addBullet('More effective parallel tool usage');

yPosition += 3;

addHeading('3.4 Memory and Context Features (Beta)', 2);
addBullet('New memory capability stores information outside context window');
addBullet('Remembers preferences and context across conversations');
addBullet('Automatic cleanup of tool interaction history');
addBullet('Intelligent context management to prevent token waste');

yPosition += 3;

addHeading('3.5 Improved Safety and Alignment', 2);
addBullet('Most aligned frontier model from Anthropic');
addBullet('Reduced sycophancy, deception, and power-seeking behaviors');
addBullet('Better defense against prompt injection attacks');
addBullet('Improved behavior to discourage delusional thinking');

yPosition += 3;

addHeading('3.6 Specialized Capabilities', 2);
addBullet('Substantial gains in reasoning and mathematics');
addBullet('Excel in cybersecurity applications');
addBullet('Strong performance in finance and research domains');
addBullet('Enhanced computer use capabilities');

// New Page - Claude Code
doc.addPage();
yPosition = margin;

addHeading('4. Claude Code Overview', 1);
addText('Claude Code is an agentic coding tool that lives in your terminal and IDE, helping developers code faster through natural language commands. It understands your codebase and executes routine tasks, explains complex code, and handles git workflows seamlessly.');

yPosition += 5;

addHeading('5. Claude Code Installation', 1);

addHeading('5.1 Terminal Installation', 2);
addText('Install Claude Code globally using npm:');
addCodeBlock('npm install -g @anthropic-ai/claude-code');

yPosition += 3;
addText('After installation, navigate to your project directory and run:');
addCodeBlock('claude');

yPosition += 5;

addHeading('5.2 VS Code Extension (Beta)', 2);
addBullet('Native VS Code extension available in beta');
addBullet('Brings Claude Code directly into your IDE');
addBullet('Real-time changes visible through dedicated sidebar panel');
addBullet('Inline diffs for easy code review');

yPosition += 5;

addHeading('6. Claude Code Key Features', 1);

addHeading('6.1 Checkpoints System', 2);
addBullet('Automatically saves code state before each change');
addBullet('Instant rollback to previous versions');
addBullet('Tap Esc twice or use /rewind command to revert');
addBullet('Enables ambitious tasks with safety net');

yPosition += 3;

addHeading('6.2 Enhanced Terminal Interface (v2.0)', 2);
addBullet('Improved status visibility');
addBullet('Searchable prompt history (Ctrl+r)');
addBullet('Easy reuse and editing of previous prompts');
addBullet('Better message rendering with large contexts');

yPosition += 3;

addHeading('6.3 Advanced Capabilities', 2);
addBullet('Queue messages while Claude is working');
addBullet('Drag-and-drop or copy/paste image files into prompts');
addBullet('MCP "project" scope for repository-level configuration');
addBullet('Thinking mode support for complex reasoning');
addBullet('@-mentions support in slash command arguments');

yPosition += 3;

addHeading('6.4 Core Functionality', 2);
addBullet('Natural language command execution');
addBullet('Codebase understanding and navigation');
addBullet('Complex code explanation');
addBullet('Git workflow automation');
addBullet('Routine task execution');

// New Page - How to Use
doc.addPage();
yPosition = margin;

addHeading('7. How to Use Claude Code', 1);

addHeading('7.1 Basic Usage', 2);
addText('Claude Code operates through natural language commands. Simply describe what you want to accomplish:');
yPosition += 3;

addText('Examples:', 11, 'bold');
addBullet('"Add a dark mode toggle to the settings page"');
addBullet('"Refactor the authentication module to use async/await"');
addBullet('"Explain how the payment processing works"');
addBullet('"Fix the TypeScript errors in the user service"');

yPosition += 5;

addHeading('7.2 Git Workflows', 2);
addText('Claude Code can handle complex git operations:');
yPosition += 3;

addBullet('Create commits with descriptive messages');
addBullet('Create and manage branches');
addBullet('Handle pull requests');
addBullet('Resolve merge conflicts');

yPosition += 5;

addHeading('7.3 IDE Integration', 2);
addBullet('Use the VS Code extension for visual feedback');
addBullet('Review changes through inline diffs');
addBullet('Monitor Claude\'s progress in the sidebar panel');
addBullet('Tag @claude in GitHub for assistance');

yPosition += 5;

addHeading('7.4 Slash Commands', 2);
addText('Claude Code supports custom slash commands for common operations. Use the /rewind command to undo recent changes.');

yPosition += 5;

addHeading('8. Best Practices and Tips', 1);

addHeading('8.1 For Claude Sonnet 4.5', 2);
addBullet('Enable extended thinking for complex coding tasks');
addBullet('Be aware that thinking mode impacts prompt caching efficiency');
addBullet('Use memory features to maintain context across sessions');
addBullet('Leverage autonomous operation for long-horizon tasks');
addBullet('Take advantage of improved parallel tool usage');

yPosition += 5;

addHeading('8.2 For Claude Code', 2);
addBullet('Use checkpoints before attempting major refactoring');
addBullet('Search prompt history (Ctrl+r) to reuse successful commands');
addBullet('Be specific with natural language instructions');
addBullet('Review changes through the VS Code extension before committing');
addBullet('Use @-mentions for targeted assistance');
addBullet('Queue up multiple tasks when appropriate');

yPosition += 5;

addHeading('8.3 General Tips', 2);
addBullet('Start with smaller tasks to understand capabilities');
addBullet('Provide context about your codebase architecture');
addBullet('Use descriptive commit messages for better history');
addBullet('Leverage thinking mode for complex problem-solving');
addBullet('Join the Discord community for support and tips');

yPosition += 10;

// Footer on last page
doc.setFontSize(9);
doc.setTextColor(128, 128, 128);
doc.text('For more information, visit: https://docs.claude.com', pageWidth / 2, pageHeight - 15, { align: 'center' });
doc.text('Documentation generated October 2025', pageWidth / 2, pageHeight - 10, { align: 'center' });

// Save the PDF
doc.save('Claude_Code_and_Sonnet_4.5_Features_Guide.pdf');
console.log('PDF generated successfully: Claude_Code_and_Sonnet_4.5_Features_Guide.pdf');
