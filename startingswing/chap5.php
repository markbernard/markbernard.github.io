<div class="panel-heading">
    <h2 class="panel-title chapter-heading">Format Menu</h2>
</div>
<div class="panel-body scroll">
<p>The format menu is pretty small. The only two options are Word Wrap and Font.</p> 
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure5-1.png" width="100%" alt="Figure 5-1: " title="Figure 5-1: " />
                <figcaption><strong>Figure 5-1:</strong> </figcaption>
            </figure>
        </div>
    </div>
<p>Font needs a custom dialog so the code for that is pretty extensive. So let’s look at Word Wrap first. In ApplicationPreferences we need to set up a value to store the user’s current selection.</p>
<pre><code class="java">public class ApplicationPreferences {
    private static final String WORD_WRAP = "word.wrap";
    
    private static boolean wordWrap;

    public static void loadPrefs(JFrame frame) {
        .
        .
        .
        wordWrap = prefs.getBoolean(WORD_WRAP, false);
    }
    
    public static void savePrefs(JFrame frame) {
        .
        .
        .
        prefs.putBoolean(WORD_WRAP, wordWrap);
    }

    public static boolean isWordWrap() {
        return wordWrap;
    }

    public static void setWordWrap(boolean wordWrap) {
        ApplicationPreferences.wordWrap = wordWrap;
    }
}
</code></pre>
<p>It’s pretty simple. First we set up a key value for the Preferences store. A field to hold the value for the currently running application. Then load and save the value in the appropriate spots. Lastly we need a getter and setter to use the value.</p>
<pre><code class="java">public JNotepad(JFrame parentFrame) {
    .
    .
    .
    textArea.setLineWrap(ApplicationPreferences.isWordWrap());
    textArea.setWrapStyleWord(true);
}
</code></pre>
<p>In the constructor for JNotepad we will set the text area line wrapping to the previously stored value. Also there is a method called setWrapStyleWord(true) that we will set to true since we want to only wrap on words. If you set this to false it will just wrap on letters.</p>
<pre><code class="java">private void createMenus() {
    .
    .
    .
    JMenu formatMenu = new JMenu(new FormatAction());
    bar.add(formatMenu);
    JCheckBoxMenuItem formatWordWrap = new JCheckBoxMenuItem(
        new FormatAction.WordWrapAction(this));
    formatMenu.add(formatWordWrap);
    formatWordWrap.setSelected(ApplicationPreferences.isWordWrap());
    JMenuItem formatFont = new JMenuItem(new FormatAction.FontAction(this));
    formatMenu.add(formatFont);
}
</code></pre>
<p>Here we set up the menu selections as before but you should notice a new class being used, javax.swing.JCheckBoxMenuItem. As the name indicates this will toggle a checkmark beside the menu item as the user clicks the option. Also by calling set selected to true or false we can programmatically display or clear the checkmark. Here we will call up the stored word wrap value and call setSelected(ApplicationPreferences.isWordWrap()) with that value. We also set up the actions in the standard way. There is nothing new with this action so I won’t show the code.</p>
<pre><code class="java">public void wordWrap() {
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            ApplicationPreferences.setWordWrap(!ApplicationPreferences.isWordWrap());
            textArea.setLineWrap(ApplicationPreferences.isWordWrap());
        }
    });
}
</code></pre>
<p>This is the method that is called by the user clicking on the menu option. We simply invert the boolean value for word wrap and set the text area word wrapping to the value stored in word wrap.</p>
<p>When the user selects Font… from the Format menu the action will call the font method in JNotepad.</p>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure5-2.png" width="100%" alt="Figure 5-2: " title="Figure 5-2: " />
                <figcaption><strong>Figure 5-2:</strong> </figcaption>
            </figure>
        </div>
    </div>
<pre><code class="java">public void font() {
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            FontDialog fontDialog = new FontDialog(parentFrame, 
                    ApplicationPreferences.getCurrentFont());
            if (fontDialog.showFontDialog()) {
                Font selectedFont = fontDialog.getSelectedFont();
                ApplicationPreferences.setCurrentFont(selectedFont);
                textArea.setFont(selectedFont);
            }
            fontDialog.dispose();
        }
    });
}
</code></pre>
<p>First we create an instance of our custom FontDialog. We need to pass in a reference to the application frame, JNotepad and the current font. Next we’ll call the custom method showFontDialog(). I’ll show the code for that later. For now you just need to know that if the user clicks the OK button then the dialog will close and this method will return true. If the user clicks Cancel or clicks the ‘X’ to close the dialog then it will return false.</p>
<p>If the user selected a font and clicked OK then we will call the method getSelectedFont() to get what the picked. Then we’ll store a reference to that font so it can be saved when the application closes. Lastly we’ll change the text area to use the new font.</p>
<p>Once we react to the result of showFontDialog() we need to properly dispose of the dialog to release all resources associated with it.</p>
<pre><code class="java">public class FontDialog extends BasicDialog implements WindowListener {
    private static final long serialVersionUID = -301017949751686897L;
    private static final String[] STYLES = {"Plain", "Bold", "Italic", "Bold Italic"};
    private static final String[] SIZES = {"8", "9", "10", "11", "12", "14", "16", "18", "20", "22", "24", "26", "28", "36", "48", "72"};
    
    private JList fontFamilyList;
    private JList fontStyleList;
    private JList fontSizeList;
    
    private JTextField fontFamilyField;
    private JTextField fontStyleField;
    private JTextField fontSizeField;
    
    private FontSample fontSample;
    
    private String currentFontFamily;
    private int currentFontStyle;
    private int currentFontSize;
    private boolean cancelled;
</code></pre>
<p>Here is the FontDialog. In the class declaration we extend our BasicDialog class. We also implement WindowListener so we can detect if the user clicks the ‘X’ instead of one of the buttons. In Java there are not any methods to interrogate what a font is capable of so we are limited to 4 styles: plain, bold, italic and bold italic. The STYLES constant array contains these names to populate the list the user will use. Practically any size can be used for a font so we’ll just maintain an array of the most popular sizes.</p>
<p>Next are the three list components that we will use to present the lists of options to the user. There are also three corresponding text fields. The FontSample is an internal class we’ll define later on that will be used to show a sample of what the font looks like as the user makes choices.</p>
<p>We will also declare three fields to hold the last value that the user has selected. There are points where we may be in transition while the user makes a selection so these fields will only be updated when a selection is completed. This is important since the user may click the Ok button before completing a selection. If they do that we may end up with an exception thrown trying to create a font out of invalid data. By maintaining the last completed selection we can prevent any error from occurring in this case. The cancelled field is set to true when the user clicks Cancel or the ‘X’ to close the dialog.</p>
<pre><code class="java">public FontDialog(JFrame owner, Font currentFont) {
    super(owner, "Font", true);
    cancelled = true;
    addWindowListener(this);
    setResizable(false);
    setLayout(new BorderLayout());
    JPanel mainPanel = new JPanel(new BorderLayout());
    add(mainPanel, BorderLayout.CENTER);
</code></pre>
<p>Here is the constructor. We need a reference to the JFrame for the application so we can tie the dialog to it. We also want to have the font that is currently being used so we can show it in the highlighted selections. We call the parent class with the information it needs. We give it the application window, the title for the title bar and that we want this dialog to be modal.</p>
<p>Next we set cancelled to true in case some unforeseen exception causes the dialog to close we want to make sure incomplete data is not used to try to create a font. We add the WindowListener so we can make sure that if the user closes the dialog with the ‘X’ that we can make sure that it is marked as cancelled. The dialog is set to not be resizable so we can better control how it looks.</p>
<p>The next three lines give us the usual settings for displaying the dialog to the user. We set the dialog window to use a BorderLayout, create a main panel that will hold all the widgets and add the main panel to the center of the dialog.</p>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure5-3.png" width="100%" alt="Figure 5-3: " title="Figure 5-3: " />
                <figcaption><strong>Figure 5-3:</strong> </figcaption>
            </figure>
        </div>
    </div>
<pre><code class="java">JPanel fontPanel = new JPanel(new BorderLayout());
mainPanel.add(fontPanel, BorderLayout.NORTH);
</code></pre>
<p>First we create the fontPanel. It will hold all the widgets responsible for allowing the user to select the different aspects of creating a font. We’ll place the fontPanel in the north of the mainPanel. In figure 5-3 the green outline represents the mainPanel. The red outline is the fontPanel. You can see how it is in the north. We want it in the north so the various selection options will all be displayed as the same height.</p>
<pre><code class="java">JPanel fontNameStylePanel = new JPanel(new BorderLayout());
fontPanel.add(fontNameStylePanel, BorderLayout.CENTER);
</code></pre>
<p>Next we create the fontNameStylePanel. This is the blue rectangle in figure 5-3. It will hold the name selections in its center and the style selections in the east. It will then be added to the center of the fontPanel. By doing it this way we can have all three sets of selections line up horizontally in the dialog.</p>
<pre><code class="java">JPanel fontNamePanel = new JPanel(new BorderLayout());
fontNameStylePanel.add(fontNamePanel, BorderLayout.CENTER);
fontNamePanel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 10));
JLabel fontLabel = new JLabel("Font:");
fontNamePanel.add(fontLabel, BorderLayout.NORTH);
currentFontFamily = currentFont.getFamily();
fontFamilyField = new JTextField(currentFontFamily);
fontNamePanel.add(fontFamilyField, BorderLayout.CENTER);
fontLabel.setLabelFor(fontFamilyField);
fontLabel.setDisplayedMnemonic(KeyEvent.VK_F); addEscapeToActionMap(fontFamilyField);
final String[] fontFamilyNames = 
    GraphicsEnvironment.getLocalGraphicsEnvironment().getAvailableFontFamilyNames();
fontFamilyList = new JList(fontFamilyNames);
addEscapeToActionMap(fontFamilyList);
JScrollPane fontListPane = new JScrollPane(fontFamilyList, 
    ScrollPaneConstants.VERTICAL_SCROLLBAR_ALWAYS, 
    ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);
fontNamePanel.add(fontListPane, BorderLayout.SOUTH);
</code></pre>
<p>This is the code for the fontNamePanel. It will go in the center of the fontNameStylePanel defined above. It will have a label in the north, a text field in the center and a list in the south. The user can click on a name in the list to choose which font they want. When they click it will populate the text field with their selection. They can also start typing a name in the text field and the list will scroll to the name they are typing if there is a match. We also call addEscapeToActionMap on fontFamilyField and fontFamilyList. Recall from earlier how this will allow us to use the ESCAPE key to cancel the dialog. We’ll also populate the text field with the current font family in use in the text field constructor and associate the label with the text field. One other thing to notice is the GraphicsEnvironment class allows us to get a list of available font family names. We can just directly use the result of this call to populate the list the user will select from.</p>
<pre><code class="java">JPanel fontStylePanel = new JPanel(new BorderLayout());
fontNameStylePanel.add(fontStylePanel, BorderLayout.EAST);
fontStylePanel.setBorder(BorderFactory.createEmptyBorder(20, 10, 20, 10));
JLabel fontStyleLabel = new JLabel("Font style:");
fontStylePanel.add(fontStyleLabel, BorderLayout.NORTH);
currentFontStyle = currentFont.getStyle() &amp; 0x03;
fontStyleField = new JTextField(STYLES[currentFontStyle]);
fontStylePanel.add(fontStyleField, BorderLayout.CENTER);
fontStyleLabel.setLabelFor(fontStyleField);
fontStyleLabel.setDisplayedMnemonic(KeyEvent.VK_Y); 
addEscapeToActionMap(fontStyleList);
JScrollPane fontStylePane = new JScrollPane(fontStyleList, 
        ScrollPaneConstants.VERTICAL_SCROLLBAR_ALWAYS, 
        ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);
fontStylePanel.add(fontStylePane, BorderLayout.SOUTH);
</code></pre>
<p>The fontStylePanel will go in the east of the fontNameStylePanel. Everything is almost exactly the same as building the UI for the names. Looking in the Java API documentation we can find out the real values for the constants used for font styles: PLAIN, BOLD and ITALIC. PLAIN is equal to zero, BOLD is one and ITALIC is two. You can also do a bitwise or of BOLD and ITALIC with the result being three. This makes it very easy to use the STYLES array defined as a constant for the FontDialog class. We can just call getSyle() on the current font to see what is being used and pass the result of that to the text field contructor. We can also select the correct index in the JList with the same number.</p>
<pre><code class="java">JPanel fontSizePanel = new JPanel(new BorderLayout());
fontPanel.add(fontSizePanel, BorderLayout.EAST);
fontSizePanel.setBorder(BorderFactory.createEmptyBorder(20, 10, 20, 20));
JLabel fontSizeLabel = new JLabel("Size:");
fontSizePanel.add(fontSizeLabel, BorderLayout.NORTH);
currentFontSize = currentFont.getSize();
fontSizeField = new JTextField(String.valueOf(currentFontSize));
fontSizePanel.add(fontSizeField, BorderLayout.CENTER);
fontSizeLabel.setLabelFor(fontSizeField);
fontSizeLabel.setDisplayedMnemonic(KeyEvent.VK_S); addEscapeToActionMap(fontSizeField);
((PlainDocument)fontSizeField.getDocument()).setDocumentFilter(new NumberDocumentFilter());
fontSizeList = new JList(SIZES);
addEscapeToActionMap(fontSizeList);
JScrollPane fontSizePane = new JScrollPane(fontSizeList, 
        ScrollPaneConstants.VERTICAL_SCROLLBAR_ALWAYS, 
        ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);
fontSizePanel.add(fontSizePane, BorderLayout.SOUTH);
</code></pre>
<p>The fontSizePanel will go in the east of the fontPanel. This will put it beside the fontNameStylePanel, which goes in the center. Again this section is almost exactly the same as the other two. The main difference here is using the NumberDocumentFilter class that we created earlier to only allow numbers to be entered in the text field.</p>
<pre><code class="java">pack();
centerDialog();
fontFamilyList.setSelectedValue(currentFontFamily, true);
fontStyleList.setSelectedValue(STYLES[currentFontStyle], true);
fontSizeList.setSelectedValue(String.valueOf(currentFontSize), true);
</code></pre>
<p>Now that all the input options are set up we can calculate the size of everything by calling pack() and then call centerDialog() to make sure it appears in the right place. We also make sure the inputs show the values of the font that is currently in use.</p>
<pre><code class="java">okButton.addActionListener(new ActionListener() {
    public void actionPerformed(ActionEvent e) {
        cancelled = false;
        setVisible(false);
    }
});

cancelButton.addActionListener(new ActionListener() {
    public void actionPerformed(ActionEvent e) {
        cancelled = true;
        setVisible(false);
    }
});
</code></pre>
<p>Next we set up ActionListeners for the OK and Cancel buttons. If the user clicks OK then we set cancelled to false. If they click Cancel then we set cancelled to true.</p>
<pre><code class="java">public boolean showFontDialog() {
    setVisible(true);
    
    return !cancelled;
}
</code></pre>
<p>The ActionListeners above work in conjunction with the showFontDialog() method. To show the dialog we call this method. The first line calls setVisible(true). Since this is a modal dialog setVisible(true) will not return until the dialog is no longer visible. One way for that to happen is to call setVisible(false) as we do in the ActionListeners for the OK and Cancel buttons. Another way is if the user clicks the X to close the dialog. The last way is if the user presses the ESCAPE key. Once that happens the next line will be executed which will return the value of not cancelled. So it will return true if the user clicks the OK button. It will return false if any other close action takes place.</p>
<p>Another piece of the puzzle for why this works is multi-threading. We have not explicitly created any threads here but calling setVisible(true) on a dialog or window causes the EDT to take over calling any methods that react to GUI manipulation. So if the user clicks the OK button then the EDT calls the actionlistener that we set up. Then by calling setVisible(false) the previous call, in a different thread, to setVisible(true) returns since the dialog is now hidden. It is important to try and understand when code may be executing in a different thread to fully comprehend how your code will execute. If you are building a GUI application and you never create a single thread yourself you will still have to deal with threads being created automatically based on certain actions. Rather than avoid threading because it is complicated, try to learn the basics of threading. That is all you need to understand how a GUI application executes.</p>
<pre><code class="java">addListEvents();
addTextFieldEvents(fontFamilyNames);
</code></pre>
<p>The next thing to do is add listeners to the list and text field widgets. This will allow us to make the UI more user friendly by reacting to user input. If the user starts typing in one of the text boxes we can scroll the list to the most likely selection that will be made. Alternately if the user clicks on an item in one of the lists then the text field will be filled out with the selected item’s name.</p>
<pre><code class="java">private void addListEvents() {
    fontFamilyList.addListSelectionListener(new ListSelectionListener() {
        public void valueChanged(ListSelectionEvent e) {
            new Thread(new Runnable() {
                public void run() {
                    currentFontFamily = (String)fontFamilyList.getSelectedValue();
                    Document doc = fontFamilyField.getDocument();
                    doc.removeDocumentListener(fontFamilyDocumentListener);
                    fontFamilyField.setText(currentFontFamily);
                    doc.addDocumentListener(fontFamilyDocumentListener);
                    updateFont();
                }
            }, "Font Family Select").start();
        }
    });
</code></pre>
<p>First we’ll add a listener to the list that has the font names in it. If the user clicks on any item in the list this valueChanged(ListSelectionEvent e) method will be called. First we will assign the selected value to be the current one. Then we will set the text field to display this value. One problem that occurs is that if we set a value on the text field it will activate any document listeners that are set up. Normally this is not a problem but for us we are going to have document listeners and list selection listeners and if we don’t disable one inside the other we will end up with an infinite loop. So we disable the listener, set the text value and then re-enable the listener. After that we finish we need the font sample panel to be updated with the new font selection. We call updateFont() to do that. We’ll look at that later.</p>
<pre><code class="java">fontStyleList.addListSelectionListener(new ListSelectionListener() {
    public void valueChanged(ListSelectionEvent e) {
        new Thread(new Runnable() {
            public void run() {
                String styleName = (String)fontStyleList.getSelectedValue();
                currentFontStyle = 0;
                for (currentFontStyle = 0;currentFontStyle &lt; STYLES.length;
                        currentFontStyle++) {
                    if (styleName.equals(STYLES[currentFontStyle])) {
                        break;
                    }
                }
                Document doc = fontStyleField.getDocument();
                doc.removeDocumentListener(fontStyleDocumentListener);
                fontStyleField.setText(STYLES[currentFontStyle]);
                doc.addDocumentListener(fontStyleDocumentListener);
                updateFont();
            }
        }, "Font Style Select").start();
    }
});
</code></pre>
<p>Next we react if the user clicks on a value in the style list. The main difference here is that we are storing the style as a number. We have to loop through the defined style names until we find the one that matches. Then we set the current style to that number. The rest is the same as the font family. Disable the document listener, set the style name to the text field, re-enable the document listener and update the font sample.</p>
<pre><code class="java">fontSizeList.addListSelectionListener(new ListSelectionListener() {
    public void valueChanged(ListSelectionEvent e) {
        new Thread(new Runnable() {
            public void run() {
                currentFontSize = Integer.parseInt((String)fontSizeList.getSelectedValue());
                Document doc = fontSizeField.getDocument();
                doc.removeDocumentListener(fontSizeDocumentListener);
                fontSizeField.setText(String.valueOf(currentFontSize));
                doc.addDocumentListener(fontSizeDocumentListener);
                updateFont();
            }
        }, "Font Size Select").start();
    }
});
</code></pre>
<p>The last list is the font size list. This one is pretty straight forward. Read the value that was selected. We know that all the values are numbers so we can parse it as an integer with no problem. Set the current font size the number selected, disable the listener, set the text field with the selected value, re-enable the listener and update the font sample.</p>
<pre><code class="java">private void addTextFieldEvents(final String[] fontFamilyNames) {
    fontFamilyField.addFocusListener(new FocusListener() {
        public void focusLost(FocusEvent e) {
        }
        public void focusGained(FocusEvent e) {
            fontFamilyField.selectAll();
        }
    });
    
    fontStyleField.addFocusListener(new FocusListener() {
        public void focusLost(FocusEvent e) {
        }
        public void focusGained(FocusEvent e) {
            fontStyleField.selectAll();
        }
    });
    
    fontSizeField.addFocusListener(new FocusListener() {
        public void focusLost(FocusEvent e) {
        }
        public void focusGained(FocusEvent e) {
            fontSizeField.selectAll();
        }
    });
</code></pre>
<p>The first thing we do in addTextFieldEvents(final String[] fontFamilyNames) is to add focus listeners to each text field. If a text field is clicked it gains focus. When this happens we select everything that is in the field. This way all the user has to do is type to start selecting an item from the list.</p>
<pre><code class="java">fontFamilyDocumentListener = new DocumentListener() {
    public void removeUpdate(DocumentEvent e) {
        checkText();
    }
    public void insertUpdate(DocumentEvent e) {
        checkText();
    }
    public void changedUpdate(DocumentEvent e) {
        checkText();
    }
    private void checkText() {
        new Thread(new Runnable() {
            @Override
            public void run() {
                ListSelectionListener[] currentListeners = 
                        fontFamilyList.getListSelectionListeners();
                for (ListSelectionListener currentListener : currentListeners) {
                    fontFamilyList.removeListSelectionListener(currentListener);
                }
                fontFamilyList.clearSelection();
                String textToCheck = fontFamilyField.getText();
                for (String fontFamilyName : fontFamilyNames) {
                    if (fontFamilyName.equalsIgnoreCase(textToCheck)) {
                        fontFamilyList.setSelectedValue(fontFamilyName, true);
                        currentFontFamily = fontFamilyName;
                        break;
                    } else if (fontFamilyName.toLowerCase()
                            .startsWith(textToCheck.toLowerCase())) {
                        fontFamilyList.setSelectedValue(fontFamilyName, true);
                        currentFontFamily = fontFamilyName;
                        break;
                    }
                }
                updateFont();
                for (ListSelectionListener currentListener : currentListeners) {
                    fontFamilyList.addListSelectionListener(currentListener);
                }
            }
        }, "Font Family Typing").start();
    }
};
fontFamilyField.getDocument().addDocumentListener(fontFamilyDocumentListener);
</code></pre>
<p>Next we create a DocumentListener for the font text field. We don't care what type of update happens. If the text field changes then we want to react to it. First we remove all listeners from the list. Next we compare what has been typed to the beginning of each name in the font list. Once we find the first match we select that value in the font list and assign it to the current font. We then update the font sample and add all the listeners back in that we removed. Once we finish defining the DocumentListener we add it to the text field's document.</p>
<pre><code class="java">fontStyleDocumentListener = new DocumentListener() {
    public void removeUpdate(DocumentEvent e) {
        checkText();
    }
    public void insertUpdate(DocumentEvent e) {
        checkText();
    }
    public void changedUpdate(DocumentEvent e) {
        checkText();
    }
    private void checkText() {
        new Thread(new Runnable() {
            @Override
            public void run() {
                ListSelectionListener[] currentListeners = 
                        fontStyleList.getListSelectionListeners();
                for (ListSelectionListener currentListener : currentListeners) {
                    fontStyleList.removeListSelectionListener(currentListener);
                }
                fontStyleList.clearSelection();
                String textToCheck = fontStyleField.getText();
                for (int i=0;i&lt;STYLES.length;i++) {
                    if (STYLES[i].equalsIgnoreCase(textToCheck)) {
                        fontStyleList.setSelectedValue(STYLES[i], true);
                        currentFontStyle = i;
                        break;
                    }
                }
                updateFont();
                for (ListSelectionListener currentListener : currentListeners) {
                    fontStyleList.addListSelectionListener(currentListener);
                }
            }
        }, "Font Style Typing").start();
    }
};
fontStyleField.getDocument().addDocumentListener(fontStyleDocumentListener);
</code></pre>
<p>The style text field works almost exactly the same way. The only difference is the list we look through for checking valid values. I won't even show the font size text field since it also works the same way.</p>
<pre><code class="java">private void updateFont() {
    fontSample.updateFont(new Font(currentFontFamily, currentFontStyle, currentFontSize));
}
</code></pre>
<p>Here is the updateFont() method. All it does is call updateFont(new Font(currentFontFamily, currentFontStyle, currentFontSize)) on the fontSample panel, passing in the font for what has been selected so far.</p>
<pre><code class="java">public Font getSelectedFont() {
    return new Font(currentFontFamily, currentFontStyle, currentFontSize);
}
</code></pre>
<p>getSelectedFont() will be called by the method that showed the dialog when the user clicks OK. It just constructs and returns a new font based on the user's choices.</p>
<pre><code class="java">public void windowClosing(WindowEvent e) {

    cancelled = true;

}
</code></pre>
<p>This dialog class we created also implements the WindowListener interface. There are 7 methods to implement for this. The only one we need is windowClosing(). This method gets called if the user clicks the 'X' to close the dialog. If they do we want to make sure the dialog is set up as cancelled.</p>
<pre><code class="java">class FontSample extends JComponent {
    private static final long serialVersionUID = 2585836584394117034L;
    private static final String DISPLAY = "AaBbYyZz";
    private static final int WIDTH = 200;
    private static final int HEIGHT = 60;
    private final Dimension SIZE = new Dimension(WIDTH, HEIGHT);
    
    private Font font;
    
    public FontSample(Font font) {
        this.font = font;
    }
    
    protected void paintComponent(Graphics g) {
        g.setFont(font);
        FontMetrics fm = g.getFontMetrics();
        int x = (WIDTH - fm.stringWidth(DISPLAY)) / 2;
        int y = ((HEIGHT - fm.getHeight()) / 2) + fm.getAscent();
        g.drawString(DISPLAY, x, y);
    }

    public Dimension getPreferredSize() {
        return SIZE;
    }
    
    public void updateFont(Font font) {
        this.font = font;
        repaint();
    }
}
</code></pre>
<p>FontSample is an inner class for the FontDialog. We initialize the class with a string to display in the selected font and the dimensions to use for the sample. To perform custom drawing on any Swing component you need to override the painComponent(Graphics g) method. This method gets called when drawing is required. This usually occurs if the window was covered and then uncovered or resized.</p>
<p>This method will get passed in an instance of a java.awt.Graphics object to perform a variety of drawing operations. There are too many to mention here, but the one we will be using is for drawing strings. I should also mention that the object that gets passed in is a java.awt.Graphics2D object. The Graphics object is left in the method signature for backwards compatibility.</p>
<p>The Graphics object provides a method called getFontMetrics(). This returns a java.awt.FontMetrics instance. This is very useful for seeing how much room our sample text will take up. By calling stringWidth(DISPLAY) on the FontMetrics instance and passing in the string that will be displayed we can see how wide it is. We then use this width to center the string horizontally in the display area.</p>
<p>An important aspect of fonts is how the height is determined and how that tells where the font will be drawn. When you provide a y coordinate for drawing a font you are saying where the baseline of the font will be drawn. There are 3 parts to a font's height: ascent, descent and leading. The ascent is how far above the baseline that the font will be drawn. If a font has an ascent of 6 and you draw it at y coordinate 0 then the top most part of the font to be drawn will occur at y coordinate -6. The descent is how far below the baseline the font will be drawn. Mostly for letters like g and p. The leading is how much distance there should be between the bottom of one line and the top of the next line. The y coordinate can then be calculated by taking the height of the visible area and subtracting the height of the font. We also have to make allowances for the ascent to properly center it. We add the ascent to the calculated value and the text will then be centered. Finally we call drawString(DISPLAY, x, y) with our text and the calculated coordinates.</p>
<p>When layout managers layout any component it almost exclusively uses the components preferred size to do the layout, especially if pack() has been called. We override getPreferredSize() to return the size that we want to use to make sure the drawing area is always the same size. </p>
<p>Lastly updateFont(Font font) is defined so we can receive any changes the user has been making to their selections. This will set the font that we are currently using to draw and then call repaint(). It is important to understand that all GUI drawing in Java is event based. Developers can specifically set up active drawing instead, but the default is passive, event based drawing. When you call repaint() you are not redrawing the component but are requesting that it will be redrawn. This request will be satisfied on the next EDT drawing pass. If you call repaint multiple times before the next pass, all requests will be ignored except the first one and the component will only be painted once. This is why you want to perform as little logic as possible in the EDT. If someone clicks a menu option and you put a lot of complex code in the listener for that option you will tie up the EDT and it will not be able to get to the drawing phase as often as it should. This causes your GUI to seem sluggish and unresponsive. This is why it is also a good idea to get into the practice of launching a new thread for every listener that you set up. Threads are extremely light weight and for most of what you might want to do will end very quickly.</p>
</div>
<div class="panel-footer foot-nav">
    <div class="row">
        <div class="col-md-4 col-sm-1"><a href="/startingswing/page.php?page=chap4">Edit Menu</a></div>
        <div class="col-md-4 col-sm-1 text-center"><a href="/startingswing/page.php?page=contents">Contents</a></div>
        <div class="col-md-4 col-sm-1"><span class="pull-right"><a href="/startingswing/page.php?page=chap6">View &amp; Help Menu</a></span></div>
    </div>
</div>
