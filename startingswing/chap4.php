<div class="panel-heading">
    <h2 class="panel-title chapter-heading">Edit Menu</h2>
</div>
<div class="panel-body scroll">
    <p>Now we can dig into the edit menu options.</p>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-1.png" width="100%" alt="Figure 4-1: " title="Figure 4-1: " />
                <figcaption><strong>Figure 4-1:</strong> </figcaption>
            </figure>
        </div>
    </div>
    <p>Most, if not all, of these options should be familiar to you if you have used any kind of text or document editor. There will be an EditAction class used here to cover all the menu options. I won’t show any of that code since it works the same way as the FileAction class. There are 11 options here but a number of them can be built off of functionality built into Swing.</p>
    <p>For undo there is a built in class to handle it. It is javax.swing.undo.UndoManager. All Swing text components have a java.text.swing.Document in them that hold onto the data (model). This class supports java.swing.event.UndoableEditListener, which UndoManager implents.</p>
<pre><code class="java">private UndoManager undoManager;
</code></pre>
    <p>To make use of this just declare it as a field.</p>
<pre><code class="java">undoManager = new UndoManager();
textArea.getDocument().addUndoableEditListener(undoManager);
</code></pre>
    <p>Then in the constructor create an instance and add it to the JTextArea’s Document.</p>
<pre><code class="java">public void undo() {
    if (undoManager.canUndo()) {
        undoManager.undo();
    }
}
</code></pre>
    <p>After that set up a method for the action to call when an undo is asked for. There is one more thing we have to do. When we load a file the UndoManager will register that as an edit that can be undone. So if someone does some edits then selects undo too many times the whole document can be wiped out. So we need to reset the UndoManager after loading a new file.</p> 
<pre><code class="java">public void loadFile(File path) {
     .
     .
     .
    textArea.setText(content.toString());
    undoManager.discardAllEdits();
    dirty = false;
     .
     .
     .
}
</code></pre>
    <p>In the loadFile() method we need to add the reset right after setting the new content.</p>
<pre><code class="java">public void cut() {
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            int start = textArea.getSelectionStart();
            textArea.cut();
            textArea.setSelectionStart(start);
            textArea.setSelectionEnd(start);
        }
    });
}
</code></pre>
    <p>All the cut option needs is a method for the action to call. It is slightly more complicated than it needs to be due to the way Swing handles cut. If you just call cut() on a JTextArea the piece of selected text will be cut but the selection won’t be cleared. The extra code is there to clear the selection after the cut has been performed. The cut() method in JTextArea places the text in the system clipboard so it will be available to all applications, not just your Swing app. We also need to send this code to the EDT since we are manipulating the GUI. I won’t mention it again but just be aware when you see SwingUtilities.invokeLater().</p>
<pre><code class="java">public void paste() {
    Clipboard clipboard = Toolkit.getDefaultToolkit().getSystemClipboard();
    DataFlavor[] flavors = clipboard.getAvailableDataFlavors();
    for (DataFlavor flavor : flavors) {
        if (flavor.isFlavorTextType() &amp;&amp; flavor.isMimeTypeSerializedObject()) {
            performPaste(flavor, clipboard);
        }
    }
}

private void performPaste(DataFlavor flavor, Clipboard clipboard) {
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            try {
                String data = (String)clipboard.getData(flavor);
                int start = textArea.getSelectionStart();
                int end = textArea.getSelectionEnd();
                int length = end - start;
                Document doc = textArea.getDocument();
                try {
                    if (length > 0) {
                        doc.remove(start, length);
                    }
                    doc.insertString(start, data, null);
                    int location = start + data.length();
                    textArea.setSelectionStart(location);
                    textArea.setSelectionEnd(location);
                } catch (BadLocationException e) {
                    //looks like there is nothing to remove
                    //if a mistake occurs we can still try standard paste
                    textArea.paste();
                }
            } catch (UnsupportedFlavorException e) {
                // generally this should not happen since we checked before hand if the flavor passed in was available.
                //if a mistake occurs we can still try standard paste
                textArea.paste();
            } catch (IOException e) {
                //if a mistake occurs we can still try standard paste
                textArea.paste();
            }
        }
    });
}
</code></pre>
    <p>The paste command is made more complicated for the same reason cut is. If you make a selection and then paste into that selection, it will be replaced by the pasted text but the selection will remain. This is not the behaviour we want in our application so we will have to write our own.</p>
    <p>The paste() method will be called from the action if the paste command is selected. The java.awt.datatransfer.Clipboard class can represent a clipboard bound to just your application if you create it with the standard constructor. If you want to access the clipboard from the underlying OS then you have use java.awt.Toolkit to get it. Once you have access to the OS clipboard you can check if there is anything there by calling getAvailableDataFlavors(). If there is nothing there this will return a zero length array. If there is one or more java.awt.datatransfer.DataFlavor objects then you run through them until you find one that contains text as a Java String. If none contain text then we will do nothing. If one or more of them is text, we will just choose the first one. Once we find one we call performPaste() passing in the selected DataFlavor and the system clipboard.</p>
    <p>In performPaste() we will get the text from the clipboard, check and store the selection area with the length of selection. If there is no selection then start and end will be the same and length will be zero. Once we get all that we need to get the Document from the JTextArea. Now we are ready to do some work. First if there is a selection then remove the text in the selection. Next insert the text from the clipboard into the current location. Move the location of the cursor to the end of the pasted text.</p>
    <p>There are a number of exceptions declared here but in the majority of cases they should not occur. The BadLocationException should not happen since we retrieved all our values directly from the document. The UnsupportedFlavorException shouldn’t occur because we are only using a DataFlavor that was available in the clipboard. The IOException would only occur if there is an issue communicating with the OS clipboard. Just in case they do occur we will fall back to the default paste on the JTextArea.</p>
<pre><code class="java">public void delete() {
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            int start = textArea.getSelectionStart();
            int end = textArea.getSelectionEnd();
            if (start != end) {
                textArea.replaceRange("", start, end);
                textArea.setSelectionEnd(start);
                textArea.setSelectionStart(start);
            }
        }
    });
}
</code></pre>
    <p>Delete is pretty much the same as cut. The only difference is that delete will not place the selection into the clipboard, it will just be removed from the text. We just have to remember that once we remove the text, we also have to clear the selection and place the cursor at the beginning of the area that was selected. If we set the selection start and end at the same position then nothing will be selected and the cursor will be placed at the same location.</p>
    <p>Find, find all and replace are interrelated and will be discussed together. </p>
<pre><code class="java">private String findTerm;
private String replaceTerm;
private boolean findDownDirection;
private boolean matchCase;
</code></pre>
    <p>First we are going to need some fields to hold information for these functions. The findTerm will hold the value of whatever we are searching for. If we are doing a replace operation then replaceTerm will hold that value. For the find and find all command the user will be able to search forward or backward in the document. The field findDirectionDown will hold which direction we are searching. And lastly we'll use matchCase to determine if the user wants to make the search case sensitive or case insensitive.</p>
    <p>For these commands we are going to have create a dialog for the user to interact with. We’ll look at the dialog first and then come back to the application code. The class in Swing for creating complex dialogs is javax.swing.JDialog. Later on we are going to have more custom dialogs so there are a couple pieces of functionality that they will all need. We are going to create a base dialog class for our other dialogs. We'll call it BasicDialog and it will extend JDialog.</p>
<pre><code class="java">public abstract class BasicDialog extends JDialog {
    private static final long serialVersionUID = 5010359693222917600L;

    public BasicDialog(Frame owner, String title, boolean modal) {
        super(owner, title, modal);
    }

    protected void addEscapeToActionMap(JComponent component) {
        component.getActionMap().put("Cancel", new DialogAction.CancelAction(this));
        component.getInputMap(JComponent.WHEN_FOCUSED)
            .put(KeyStroke.getKeyStroke(KeyEvent.VK_ESCAPE, 0), "Cancel");
    }

    protected void centerDialog() {
        Window frameOwner = getOwner();

        int width = getWidth();
        int height = getHeight();
        if (width &lt;= 0) {
            width = 350;
        }
        if (height &lt;= 0) {
            height = 130;
        }
        setLocation(frameOwner.getX() + (frameOwner.getWidth() - width) / 2,
                frameOwner.getY() + (frameOwner.getHeight() - height) / 2);
    }
}

</code></pre>
    <p>We only need one constructor so we will override that so we can send the correct values to the JDialog class. All Swing components contain an ActionMap and InputMap. The ActionMap contains actions stored with a key. This key can be used to relate actions to entries in the InputMap. The InputMap connects key combinations to action keys in the ActionMap. All interactive Swing widgets come with some predetermined entries for these maps. When an interactive widget has the focus it will capture and process all keyboard events. So in order to gain some overriding behaviours we need to put our own key combinations and actions in these maps. In the case of dialogs we want to dismiss the dialog if the user presses the ESC key. In the find and replace dialogs we have nine interactive widgets that we need to apply the ESC key to. With that in mind we’ll create the method addEscapeToActionMap(JComponent component) which we can call when creating the GUI for dialogs that we want to be able to dismiss with the ESC key.</p>
    <p>If you don't do anything after creating a dialog and just show it, it will end up in the top left corner of your screen. It is more user friendly to show it over the top of your application so the user can see it appear. The centerDialog() method achieves this. Here we will make it easy and just center the dialog on top of our applciation. The JDialog keeps track of the owner window so in the first line we can get a reference to that. Then we just subtract the width of our dialog from the width of our owner window and divide by 2. Add the result to the left side of the owner window. Do the same for the height and add the result to the top of the owner. Call setLocation(int x, int y) with the results and the dialog will be in the center of our application window.</p>
    <p>The find command and replace command are going to share the same dialog since the options for both are very similar. The class we are going to create is called SearchDialog and will extend BasicDialog. There is a lot going on here so we’ll go through it piece by piece.</p>
<pre><code class="java">public class SearchDialog extends BasicDialog implements DocumentListener {
    private JTextField findField;
    private JTextField replaceField;
    private JCheckBox matchCase;
    private JRadioButton upRadio;
    private JRadioButton downRadio;
    private JButton findNextButton;
    private JButton replaceButton;
    private JButton replaceAllButton;
    private boolean replace;
</code></pre>
    <p>First we’ll declare a number of private fields for handling user input. The javax.swing.JTextField class presents a single line text input field. The javax.swing.JCheckBox class presents a box that the user can use to toggle on or off. It shows a checkmark when on and is empty when off. The javax.swing.JRadioButton class shows a circle the user can click to select. By itself the user cannot deselect a radio button. However we’ll see later how to use a javax.swing.ButtonGroup to allow this to happen. The javax.swing.JButton class represents your standard button widget that you can click to cause an action. You’ll also notice that we are implementing the javax.swing.event.DocumentListener interface. This is going to be shown after the dialog constructor is shown.</p>
<pre><code class="java">public SearchDialog(JFrame frame, JNotepad jNotepad, boolean replace) {
    super(frame, (replace ? "Replace" : "Find"), false);
    this.replace = replace;
    setLayout(new BorderLayout());
</code></pre>
    <p>The super class constructor we want to call takes three arguments. The first is the parent of this dialog. The parent can be any class that inherits java.awt.Window. Our application is in a JFrame which is a child class of Window. The second argument is the text that will appear in the title of the dialog. The third argument is a boolean that when set to true will make the dialog modal. False will make it non-modal. If a dialog is modal then it prevents interaction with the designated parent. Since we are trying to mimic Notepad as closely as possible, we have made this dialog non-modal so the user can interact with the text area. It makes sense to allow this anyway since the user may want to get another piece of text to place in the find input field. The boolean for replace is set to true if we are going to show the replace dialog or false if we are going to show the find dialog.</p>
    <p>The call to setLayout(new BorderLayout()) sets which layout manger to use for drawing components. The placement of visual components is all handled by layout managers. All the layout managers implement the java.awt.LayoutManager interface. Layout managers were used originally with AWT GUIs and continue to be used with Swing. The three main ones that I use are BorderLayout, FlowLayout and GridLayout. I find they handle 99% or more cases so I don’t really need the others, except in very specific cases. Some people like to use GridBagLayout for complicated layouts. Either way you are going to have a similar amount of code. I prefer to not use GridBagLayout unless absolutely necessary.</p>
    <p>BorderLayout has only 5 locations that you can place a component. They are the center, left, right, top and bottom. There are constants defined in the BorderLayout class to handle this when adding a component to the container. They are CENTER, WEST, EAST, NORTH and SOUTH. Layout managers rely on the component for hints about how they should be laid out. In particular they will call getPreferredSize() on each component and will try to honor the returned value, according to the layout manager rules. The value returned from getPreferredSize() is a java.awt.Dimension instance. The Dimension class contains the width and height that the component needs to be displayed properly. When you place a component in the north or south the height will be honored so the component will be draw no higher than requested. However for the width they will be drawn to fill the full width of the container that is being laid out. For the west and east they are similar to norht and south except that the width property will be honored and the component will be drawn as tall as the parent component is, except if there are north and/or south components they will stop at the edge of those ones. When a component is placed in the center neither property is honored. Instead the component will be drawn taking all available remaining space that is not used by the other four sections. This code and screenshot will show BorderLayout in action.</p>
<pre><code class="java">JFrame f = new JFrame("Test");
f.setLayout(new BorderLayout());
f.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
f.add(new JButton("NORTH"),  BorderLayout.NORTH);
f.add(new JButton("SOUTH"),  BorderLayout.SOUTH);
f.add(new JButton("WEST"),  BorderLayout.WEST);
f.add(new JButton("EAST"),  BorderLayout.EAST);
f.add(new JButton("CENTER"),  BorderLayout.CENTER);
f.setSize(300, 300);
f.setVisible(true);
</code></pre>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-2.png" width="100%" alt="Figure 4-2: " title="Figure 4-2: " />
                <figcaption><strong>Figure 4-2:</strong> </figcaption>
            </figure>
        </div>
    </div>
    <p>FlowLayout works very different from BorderLayout. Components will either be left justified, right justified or centered. Components will be drawn in the order that they are added to the container with respect to the java.awt.ComponentOrientation of the container. The ComponentOrientation is used to determine directionality with respect to language. In my case this will be left to right, top to bottom. Both the width and height will be honored as best as possible. As an example if you were to set the layout to right justified and add 2 buttons, button A and button B, button B will be drawn on the right edge of the component. Button A will be drawn next to and on the left of button B. Here is some code followed by a screenshot to show how this works.</p>
<pre><code class="java">JFrame f = new JFrame("Test");
f.setLayout(new BorderLayout());
f.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
JPanel p = new JPanel(new FlowLayout(FlowLayout.RIGHT));
f.add(p, BorderLayout.NORTH);
p.add(new JButton("Button A"));
p.add(new JButton("Button B"));
f.setSize(300, 100);
f.setVisible(true);
</code></pre>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-3.png" width="100%" alt="Figure 4-3: " title="Figure 4-3: " />
                <figcaption><strong>Figure 4-3:</strong> </figcaption>
            </figure>
        </div>
    </div>
    <p>This also shows how you can combine LayoutManagers to achieve the desired result. To get the buttons in the top right side of the window I placed the buttons in a container with FlowLayout set. Then I placed that container in the north of another one that was using BorderLayout. You can also see that there is a gap between the two buttons. This a feature of these three managers. You can provide a horizontal and vertical gap between components that this manager is laying out. BorderLayout and GridLayout defaults these gaps to zero and only puts space between components that are being laid out. FlowLayout defaults these gaps to 5 and puts this space between components in the same layout as well as the components and the border of the container.</p>
    <p>GridLayout arranges all the components that it has in a grid pattern. You define the size of the grid and then add each component. GridLayout also uses ComponentOrientation to layout the components. In the case of English it’s left to right then top to bottom. All components in the grid will be laid out in the exact same manner. GridLayout checks the space that it has and attempts to respect the largest width and the largest height. It will then make all components the same size. Here's some code and screenshot of GridLayout in action.</p>
<pre><code class="java">JFrame f = new JFrame("Test");
f.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
f.setLayout(new GridLayout(3, 4));
f.add(new JButton("Button 1"));
f.add(new JButton("Button 2"));
f.add(new JLabel("Label 3"));
f.add(new JButton("Button 4"));
f.add(new JButton("Button 5"));
f.add(new JCheckBox("Checkbox 6"));
f.add(new JButton("Button 7"));
f.add(new JButton("Button 8 with a long name"));
f.add(new JButton("Button 9"));
f.add(new JTextField("Textfield 10"));
JPanel p = new JPanel(new FlowLayout());
f.add(p);
p.add(new JButton("Button 11"));
f.add(new JButton("Button 12"));
f.pack();
f.setVisible(true);
</code></pre>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-1.png" width="100%" alt="Figure 4-4: " title="Figure 4-4: " />
                <figcaption><strong>Figure 4-4:</strong> </figcaption>
            </figure>
        </div>
    </div>
    <p>You can see that it doesn’t matter which component you use they are all laid out to be the same size, except button 11. The reason for this is that instead of putting the button directly in the JPanel with the GridLayout we put it in a JPanel with a FlowLayout and then put that panel in the grid. This allows us to modify the behaviour to suit our needs.</p>
<pre><code class="java">JPanel mainPanel = new JPanel(new BorderLayout());
add(mainPanel, BorderLayout.CENTER);
mainPanel.setBorder(BorderFactory.createEmptyBorder(11, 5, 11, 5));
</code></pre>
    <p>Next you can see that we are creating a JPanel and putting it in the center of the dialog. This panel will hold all our visual components. Using a panel to hold components instead of adding them directly to the dialog gives you more control over the visual elements. One of these controls is the ability to add a border to a panel. JDialog and JFrame don’t have this capability. Adding borders and other types of spacing is important to the final look of any GUI element you create. The empty space in your GUI is as important as the visual elements. This allows you to create GUIs that don’t look cluttered and messy. If you look at the next two screenshots you can see this. Figure 4-5 shows the Find dialog with no borders or spacing. Figure 4-6 shows how we are going to create it with all the needed borders and white space.</p>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-5.png" width="100%" alt="Figure 4-5: " title="Figure 4-5: " />
                <figcaption><strong>Figure 4-5:</strong> </figcaption>
            </figure>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-6.png" width="100%" alt="Figure 4-6: " title="Figure 4-6: " />
                <figcaption><strong>Figure 4-6:</strong> </figcaption>
            </figure>
        </div>
    </div>
    <p>For our main panel I have used the BorderFactory class to add an empty border to the main panel. The call to createEmptyBorder(11, 5, 11, 5) takes 4 arguments. These arguments determine the space around the panel and the components it contains. In order it determines the top, right, bottom and left space. In this dialog that would mean 11 for the top and bottom and 5 for the left and right.</p>
<pre><code class="java">JPanel inputPanel = new JPanel(new BorderLayout());
mainPanel.add(inputPanel, BorderLayout.CENTER);
JPanel textInputPanel = new JPanel(new BorderLayout(10, 0));
inputPanel.add(textInputPanel, BorderLayout.NORTH);
JPanel findLabelPanel = new JPanel(new GridLayout(0, 1, 5, 5));
textInputPanel.add(findLabelPanel, BorderLayout.WEST);
JPanel findInputPanel = new JPanel(new GridLayout(0, 1, 5, 5));
textInputPanel.add(findInputPanel, BorderLayout.CENTER);
JLabel findLabel = new JLabel("Find what:");
findLabelPanel.add(findLabel, BorderLayout.WEST);
findField = new JTextField(21);
addEscapeToActionMap(findField);
findInputPanel.add(findField, BorderLayout.CENTER);
findLabel.setLabelFor(findField);
findLabel.setDisplayedMnemonic(KeyEvent.VK_N);
if (replace) {
    JLabel replaceLabel = new JLabel("Replace with:");
    findLabelPanel.add(replaceLabel, BorderLayout.WEST);
    replaceField = new JTextField(21);
    addEscapeToActionMap(replaceField);
    findInputPanel.add(replaceField, BorderLayout.CENTER);
    replaceLabel.setLabelFor(replaceField);
    replaceLabel.setDisplayedMnemonic(KeyEvent.VK_P);
}
</code></pre>
    <p>Next we’ll add the text box that will allow the user to enter search terms and replace terms. You can see in just this little snippet of code that there are a lot of panels used. While it may seem like a lot of work at first, the more often you do it the easier it becomes. If this seems a bit tedious the most popular Java IDEs have visual GUI builder tools. When I learned Swing these tools were not very mature and produced bad code. I preferred to hand code GUIs and have gotten so proficient that I can do it just as fast as using the visual tools. I would recommend hand coding a few GUIs yourself to fully understand the intricacies involved in embedding panels in panels with different layouts to get the desired results. After that experiment with the visual builders to see which you prefer.</p>
    <p>The first panel created is the inputPanel. It will contain all the input elements on the dialog. This includes the search text, replace text, match case checkbox and the directional radio buttons. It will have a BorderLayout and be placed in the center of the main panel.</p>
    <p>The textInputPanel will hold the labels and text fields for the search term and replace term. Any time you have 2 or more input fields with labels I find the best layout is to have a panel that holds them all and is set to BorderLayout. Then have one panel for all the labels set to GridLayout and placed in the west of the BorderLayout panel. Then place all the input fields in another GridLayout placed in the center of the BorderLayout. Since there are an equal number of labels to input fields they will always line up because the BorderLayout will always make both panels the same height. With textInputPanel placed in the north of the mainPanel the final piece falls into place. Being placed in the north will squeeze everything down to the preferred height. Figure 4-7 shows this working.</p>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-7.png" width="100%" alt="Figure 4-7: " title="Figure 4-7: " />
                <figcaption><strong>Figure 4-7:</strong> </figcaption>
            </figure>
        </div>
    </div>
    <p>You can see that even though the label is only 16 pixels high it is fit into a space of 20 pixels to accommodate the text field height of 20. There is also an empty border of 5 pixels between components in the same panel. This is accomplished in the constructor of GridLayout.</p> 
    <p>When you are creating a GUI that has text fields with labels to describe them it is a good idea to create an association between these elements. One reason for this is that OS level accessibility tools will be able to better describe your GUI elements to those that need them. Secondly you can apply a mnemonic character to the label that will allow the user to quickly select the text field for entry. You can see one example if this with findLabel. Once you create it and add it to the panel you call setLabelFor(findField) and pass in findField, which is our text field for this label. The next line calls setDisplayedMnemonic(KeyEvent.VK_N) which we us the letter N as our mnemonic. There are four labels for text fields in this dialog and if you look at each one you can see that they all have mnemonics.</p>
    <p>For the replace term input field we need to check the boolean, replace, that was passed in. If it is true we will show the replace term field and label, otherwise just the find field and label are shown.</p>
<pre><code class="java">if (!replace) {
    JPanel radioButtonPanel = new JPanel(new FlowLayout(FlowLayout.LEFT));
    inputPanel.add(radioButtonPanel, BorderLayout.EAST);
    upRadio = new JRadioButton("Up");
    addEscapeToActionMap(upRadio);
    radioButtonPanel.add(upRadio);
    upRadio.setMnemonic(KeyEvent.VK_U);
    downRadio = new JRadioButton("Down");
    addEscapeToActionMap(downRadio);
    radioButtonPanel.add(downRadio);
    downRadio.setMnemonic(KeyEvent.VK_D);
    downRadio.setSelected(true);
    radioButtonPanel.setBorder(BorderFactory.createTitledBorder("Direction"));
    ButtonGroup buttonGroup = new ButtonGroup();
    buttonGroup.add(upRadio);
    buttonGroup.add(downRadio);
}
</code></pre>
    <p>The panel for the up and down radio buttons will only be shown in the Find dialog. The panel that holds the radio buttons will be set to a FlowLayout. This will make both radio buttons only as big as they need to be. Then the radioButtonPanel will be placed in the east of the inputPanel. This will put it under the text input fields on the right side. We will not be reacting to changes in the radio buttons, we will only be reading their state when appropriate so setting up an action will be overkill for these. However we still want to set up a mnemonic key to make it easier for the user to make a selection. The call to setMnemonic(int mnemonic) will take care of that for each radio button. We also need to set one of the radio buttons to be selected when the dialog is first created otherwise the user may click the Find Next button without realizing that a selection has not been made. Most of the time users will want to search forward in their documents so we’ll make down the default. Next we will set a titled border on the radioButtonPanel. We are using the simplest factory method here. Just pass in a string and an etched border with a title on it will be drawn. Other factory methods for titled border allows you to choose a different type of border, set the font, color and position of the title. The last thing you see here is an instance of the class javax.swing.ButtonGroup being created and both radio buttons added to it. This allows you to choose groups of radio buttons that will only allow a single radio button to be selected at a time. You can have as many button groups as you want. Each group will act independent of the other groups. All radio buttons in a single group will allow only one to be selected at any one time.</p>
<pre><code class="java">JPanel checkBoxPanel = new JPanel(new BorderLayout());
inputPanel.add(checkBoxPanel, BorderLayout.WEST);
matchCase = new JCheckBox(new DialogAction.MatchCaseAction());
addEscapeToActionMap(matchCase);
checkBoxPanel.add(matchCase, BorderLayout.SOUTH);
</code></pre>
    <p>There is one more piece of information the user can provide to modify the search. We’ll put a checkbox in to allow them to select whether they want the search to be case sensitive or case insensitive. We’ll create a checkBoxPanel set to use a BorderLayout and place it in the west of the inputPanel. Then we’ll put the checkbox in the south of the checkBoxPanel. This will place the match case JCheckBox on the bottom left of the dialog.</p> 
<pre><code class="java">public MatchCaseAction() {
    putValue(MNEMONIC_KEY, KeyEvent.VK_C);
    putValue(DISPLAYED_MNEMONIC_INDEX_KEY, 6);
    putValue(NAME, "Match case");
}
</code></pre>
    <p>Even though we just want to read the state of match case when necessary we still use an action here to set it up. The reason for this is the way mnemonics work. If we just set the mnemonic to use the letter ‘C’ then the ‘C’ in match will be underlined. We want the ‘C’ in case to be underlined so it is more obvious. We can do that in an action with this code.</p>
    <p>The DISPLAYED_MNEMONIC_INDEX_KEY allows us to set a value that will change the character that will be underlined. You just count from the begging of your string starting at zero. So for us the sixth character is the one we want to underline.</p>
<pre><code class="java">JPanel eastPanel = new JPanel(new BorderLayout());
mainPanel.add(eastPanel, BorderLayout.EAST);
eastPanel.setBorder(BorderFactory.createEmptyBorder(0, 15, 5, 5));
JPanel buttonPanel = new JPanel(new GridLayout(0, 1, 5, 5));
eastPanel.add(buttonPanel, BorderLayout.NORTH);
findNextButton = new JButton(new EditAction.FindNextAction(jNotepad, this));
addEscapeToActionMap(findNextButton);
buttonPanel.add(findNextButton);
findNextButton.setEnabled(false);
JRootPane rootPane = getRootPane();
rootPane.setDefaultButton(findNextButton);
if (replace) {
    replaceButton = new JButton(new DialogAction.ReplaceAction(jNotepad, this));
    addEscapeToActionMap(replaceButton);
    buttonPanel.add(replaceButton);
    replaceButton.setEnabled(false);
    replaceAllButton = new JButton(new DialogAction.ReplaceAllAction(jNotepad, this));
    addEscapeToActionMap(replaceAllButton);
    buttonPanel.add(replaceAllButton);
    replaceAllButton.setEnabled(false);
}
JButton cancel = new JButton("Cancel");
addEscapeToActionMap(cancel);
buttonPanel.add(cancel);
cancel.addActionListener(new ActionListener() {
    public void actionPerformed(ActionEvent e) {
        setVisible(false);
        dispose();
    }
});
</code></pre>
    <p>The last visual elements to add are the buttons. If the Find dialog is shown there are only two buttons. If the replace dialog is shown there are four. First we want all the buttons to appear beside the input area so we create eastPanel, with a BorderLayout, and put it in the east of the mainPanel. Then we create buttonPanel, with a GridLayout, and put it in the north of the eastPanel. All the buttons will then be added to buttonPanel. This will make all the buttons the same size, have some white space between then and place them in the top right side of the dialog box.</p>
    <p>For the cancel button we want to hide the dialog if is clicked. But a reference is held onto by the EDT when you create dialogs and frames. When you are done with it you must call the dispose() method or you will end up with a memory leak.</p>
    <p>When creating findNextButton you can see our first re-use of an action. We are putting the FindNextAction class from the edit menu action of the same name to use here. Once I finish discussing the SearchDialog I will show you the expanded FindNextAction class.</p> 
    <p>Swing supports the concept of a default button. When using a GUI a default button is painted slightly differently to show that it is the default. When a default button is used the user can simple just press the enter key, once all the selections are made, to start whatever process they are trying to do. With our search dialog the user can press enter to click the Find Next button, once they are ready to search. To accomplish this we need to get the root pane from the dialog and call setDefaultButton(findNextButton) passing in our findNextButton.</p>
    <p>You may ask, “What is a root pane?” Well top level containers come with special containers. They are the root pane, layered pane, content pane and glass pane. They each perform a different function. There is too much to be said about these different containers to cover here right now. For our purposes we indirectly use the content pane because it will hold any GUI widgets that are added to the top level container. The root pane allows us to set the default button. We will make no direct use of any of these components.</p>
    <p>If we are showing the replace dialog then the replace next and replace all buttons will be added to the grid. They also have their own actions that will be discussed after we are finished with the SearchDialog. You should also notice that the first three buttons have been disabled. This is because we don’t want to try to search for something if nothing has been input in to the search term. We will use the DocumentListener to determine whether the buttons should be enabled or not. That will be shown shortly.</p>
    <p>The last button to be added is the cancel button. What is done here is pretty simple. If the user clicks cancel we just want to hide the dialog. We just set up an anonymous ActionListener class that will hide the dialog if the cancel button is clicked.</p>
<pre><code class="java">addWindowListener(new WindowAdapter() {
    public void windowDeactivated(WindowEvent e) {
        dispose();
    }
});
</code></pre>
    <p>We also want to check if the user clicks the ‘X’ to close the dialog so we can dispose of it properly.</p>
<pre><code class="java">setResizable(false);
pack();
findField.requestFocusInWindow();
centerDialog();
findField.getDocument().addDocumentListener(this);
</code></pre>
    <p>The first line lets us prevent the user from change the size of the dialog. In general you don’t want users changing the shape of the dialog because it would just not look right. Next pack() is an important method that most, if not all, dialogs should use. It will take all GUI widgets down as close to each of their preferred sizes as the layout managers will allow. So it literally packs it all as close as possible to each other. We did not call pack() on our main application due to the way JTextArea works. JTexArea does not have a preferred size so if we call pack() there it will be difficult for the user to see what is going on since the editing area will be extremely small. The requestFocusInWindow() call on the text field will place the cursor in the find term text field when the dialog is displayed. It needs to occur after either a pack() call or a setVisible() call. The reason for this is that a call to pack() realizes a frame or dialog. When we use the term realize it means that everything is set up for the GUI to become visible short of making it visible. It needs to be in this state for requesting focus on a component. We then call the method to center the dialog on the parent frame. The last line adds our document listener to the findField document so we can set the state for the control buttons.</p> 
<pre><code class="java">public void removeUpdate(DocumentEvent e) {
    checkIfText();
}

public void insertUpdate(DocumentEvent e) {
    checkIfText();
}

public void changedUpdate(DocumentEvent e) {
    checkIfText();
}

private void checkIfText() {
    if (findField.getText().isEmpty()) {
        findNextButton.setEnabled(false);
        if (replaceButton != null) {
            replaceButton.setEnabled(false);
        }
        if (replaceAllButton != null) {
            replaceAllButton.setEnabled(false);
        }
    } else {
        findNextButton.setEnabled(true);
        if (replaceButton != null) {
            replaceButton.setEnabled(true);
        }
        if (replaceAllButton != null) {
            replaceAllButton.setEnabled(true);
        }
    }
}
</code></pre>
    <p>For the DocumentListener interface we have to implement removeUpdate, insertUpdate and changedUpdate. We don’t care what the text is in the text field we just want to know if there is text. So for all three we call the checkIfText() method. In that method we just disable all buttons if the text field is empty. If it is not empty we enable all the buttons.</p>
<pre><code class="java">public String getFindTerm() {
    return findField.getText();
}

public String getReplaceTerm() {
    return replaceField.getText();
}

public boolean isFindDirectionDown() {
    if (replace) {
        return true;
    } else {
        return downRadio.isSelected();
    }
}

public boolean isMatchCase() {
    return matchCase.isSelected();
}
</code></pre>
    <p>The last thing to do is set up some methods so the information provided to the dialog can be accessed. Here we set up gettor methods to get the search term, replace term, search direction and whether or not the search is case sensitive.</p>
<pre><code class="java">public static class FindNextAction extends AbstractAction {
    private static final long serialVersionUID = 1215020210624036643L;
    private JNotepad jNotepad;
    private SearchDialog searchDialog;

    public FindNextAction(JNotepad jNotepad, SearchDialog searchDialog) {
        this.jNotepad = jNotepad;
        this.searchDialog = searchDialog;
        if (searchDialog == null) {
            putValue(Action.MNEMONIC_KEY, KeyEvent.VK_N);
        } else {
            putValue(MNEMONIC_KEY, KeyEvent.VK_F);
        }
        putValue(Action.ACCELERATOR_KEY, KeyStroke.getKeyStroke(KeyEvent.VK_F3, 0));
        putValue(Action.NAME, "Find Next");
    }

    public void actionPerformed(ActionEvent e) {
        new Thread(new Runnable() {
            
            public void run() {
                if (searchDialog != null) {
                    jNotepad.setFindTerm(searchDialog.getFindTerm());
                    jNotepad.setFindDownDirection(searchDialog.isFindDirectionDown());
                    jNotepad.setMatchCase(searchDialog.isMatchCase());
                }
                jNotepad.findNext();
            }
        }, "Find Next (Menu)").start();
    }
}
</code></pre>
    <p>Here the FindNextAction is made different than the rest of the actions form the Edit menu by its use in the SearchDialog. In the constructor the SearchDialog is passed in as an argument. If the reference is null then it means that it was called from the main app when creating the menu. If it is not null then it means it was called from the SearchDialog when creating the dialog. If it is null then we want the mnemonic to be ‘N’ for use in the menu system. Otherwise we want the mnemonic to be ‘F’ for use in the dialog. The other change is in the actionPerformed(ActionEvent e) method. If the searchDialog reference is not null then we want to copy the settings from the dialog into the main application before calling findNext().</p>
<pre><code class="java">public void find() {
    SearchDialog dialog = new SearchDialog(parentFrame, this, false);
    dialog.setVisible(true);
}
</code></pre>
    <p>Back in the application here is the find() method for the Edit -> Find menu option. This is where the dialog we have been creating comes into play. We create an instance of the search dialog and pass in the JFrame, our application reference and false to indicate that this is the find dialog. We next show the dialog. In dialogs the setVisible(true) method works differently than in frames. If a dialog is modal then this method will block and won’t return until the dialog is dismissed. The user can dismiss a dialog by clicking the ‘X’ to close it. They can also dismiss it if they click our cancel button. Since our cancel but hides the dialog this will cause the call to setVisible(true) to return. We have set up our SearchDialog class to be non-modal. In this case the call to setVisible(true) will return immediately and the find() method will return. Since the find() method has exited the reference we had to the SearchDialog is now lost. This is not a problem since we don’t need it. The EDT will hold a reference to it until we call dispose() on the dialog. For the SearchDialog this is handled inside the dialog it self. At this point it will be eligible for garbage collection. I mention this here since there may be some consideration of memory leaks with dialogs if they are not being handled properly.</p>
<pre><code class="java">public void findNext() {
    if (findTerm != null &amp;&amp; !findTerm.isEmpty()) {
        String localFindTerm = findTerm;
        if (!matchCase) {
            localFindTerm = localFindTerm.toLowerCase();
        }
        if (findDownDirection) {
            int findStart = textArea.getSelectionEnd();
            String text = textArea.getText();
            if (!matchCase) {
                text = text.toLowerCase();
            }
            int index = text.indexOf(localFindTerm, findStart);
            if (index &gt; -1) {
                textArea.setSelectionStart(index);
                textArea.setSelectionEnd(index + findTerm.length());
            }
        } else {
            int findStart = textArea.getSelectionStart();
            String text = textArea.getText().substring(0, findStart);
            if (!matchCase) {
                text = text.toLowerCase();
            }
            int index = text.lastIndexOf(localFindTerm);
            if (index &gt; -1) {
                textArea.setSelectionStart(index);
                textArea.setSelectionEnd(index + findTerm.length());
            }
        }
    } else {
        find();
    }
}
</code></pre>
    <p>The method findNext() can be called from 2 different locations. It will be called when the user selects from the menu Edit -> Find Next. It will also be called when the SearchDialog is shown and the user clicks the Find Next button. If there is no findTerm currently stored then we will just call the find() method to show the user the dialog. If there is a findTerm and it is not an empty string we will process it. First we make a local copy of the findTerm so we can change the case if it is necessary. Next we need to do things a bit different depending if the direction is up or down.</p>
    <p>When the direction is down we want to set the start location of our search to the end of any selection that has been made. This is especially important since a lot of the time we may be coming here right after the user has already found and highlighted one term already. If the term we are searching for is already highlighted and we set the start point to the beginning of the selection then we will just find the same term we already found. Next we’ll pull the text out of the text area and change it to lower case if matchCase is not selected. Next we’ll use the indexOf() method in the String class to look for our findTerm beginning where we want it to. indexOf() will return -1 if no occurrence was found. If something is found we will highlight the occurrence by setting the selection start and end appropriately.</p>
    <p>If the direction is up we want to set our start point to the selection start, since we will be working backwards. Once we have our start point we will only pull out the text from the beginning of the document to the start point. If necessary we will switch it to lowercase. Then we will find the last occurrence in the search area. Since we are only take a working section of text as opposed to the whole thing then get the last occurrence in the selection portion is the same as searching backward. Again we check for -1 to see if there is a result or not and then highlight the term appropriately.</p>
<pre><code class="java">public void replace() {
    JNotepad self = this;
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            SearchDialog dialog = new SearchDialog(parentFrame, self, true);
            dialog.setVisible(true);
        }
    });
}
</code></pre>
    <p>The replace() method is called from the Edit -> Replace menu selection. It is practically identical to the find() method. The only difference is passing in true as the third parameter to tell SearchDialog that we want the replace dialog instead of the find dialog. You have seen all the code for the replace dialog, the only thing you haven’t seen is what it looks like. Here is a screenshot of the replace dialog.</p>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-8.png" width="100%" alt="Figure 4-8: " title="Figure 4-8: " />
                <figcaption><strong>Figure 4-8:</strong> </figcaption>
            </figure>
        </div>
    </div>
    <p>The Find Next button is the same button and works the same way as in the Find dialog. The Replace button will invoke the action shown below.</p>
<pre><code class="java">public void actionPerformed(ActionEvent arg0) {
    new Thread(new Runnable() {
        public void run() {
            jNotepad.setFindTerm(searchDialog.getFindTerm());
            jNotepad.setReplaceTerm(searchDialog.getReplaceTerm());
            jNotepad.setMatchCase(searchDialog.isMatchCase());
            jNotepad.performReplace();
        }
    }, "Replace (dialog)").start();
}
</code></pre>
    <p>This will set the appropriate values in our application and then call performReplace() to process the values the user selected.</p>
<pre><code class="java">public void performReplace() {
    if (findTerm != null &amp;&amp; replaceTerm != null &amp;&amp; !findTerm.isEmpty() &amp;&amp; 
            textArea.getSelectionStart() != textArea.getSelectionEnd()) {
        String selectedText = textArea.getSelectedText();
        if ((matchCase &amp;&amp; findTerm.equals(selectedText)) || 
                (!matchCase &amp;&amp; findTerm.equalsIgnoreCase(selectedText))) {
            textArea.replaceSelection(replaceTerm);
            textArea.setSelectionStart(textArea.getSelectionEnd());
            findNext();
        }
    }
}
</code></pre>
    <p>First we need to perform the safety checks to make sure we don’t get any exceptions. We also need to make sure that the find term is not an empty string, otherwise we won’t find anything. However, the replace term can be an empty string. It is perfectly valid if the user wants to remove all instances of a specific term for phrase. When doing these kinds of operations the user will usually click the find next button to look for the first occurrence of the term they want to find. When they do this the term will be highlighted in the editing area. Next they will click the replace button to perform a single replace. After the replacement has been completed the user will expect the next occurrence of the findTerm to be highlighted. That is what we are going to do here. We get the highlighted string and compare it, with the appropriate case check, to the findTerm. If there is a match we tell the text area to replace the highlighted text with the replaceTerm. The selected range does not change with this operation so we have to de-select everything and then call findNext() to highlight the next term. Also note that if nothing is selected in the text area then null will be returned. To avoid a NullPointerException we will use findTerm and call equals() on it since we know already that it is not null.</p>
    <p>When the user clicks the replace all button the action that gets invoked does the same thing as the replace action to set up the user’s values. The only difference is the method call into our application. This is listed below.</p>
<pre><code class="java">public void replaceAll() {
    if (findTerm != null &amp;&amp; replaceTerm != null &amp;&amp; !findTerm.isEmpty()) {
        textArea.setCaretPosition(0);
        findDownDirection = true;
        findNext();
        while (findTerm.equals(textArea.getSelectedText())) {
            textArea.replaceSelection(replaceTerm);
            textArea.setSelectionStart(textArea.getSelectionEnd());
            findNext();
        }
    }
}
</code></pre>
    <p>This method works that same as the previous replace method except that instead of an “if” statement checking the selection we use a while loop. We will continue calling findNext() and looping until nothing is found. If findNext() doesn’t find anything then nothing will be highlighted and the while loop will exit.</p>
    <p>Edit -> Go To is the next item to look at. It has a method call in our main application and a custom dialog. Let’s look the method call first.</p>
<pre><code class="java">public void goTo() {
    JNotepad self = this;
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            GoToDialog goToDialog = new GoToDialog(parentFrame, self);
            if (goToDialog.showDialog()) {
                int lineNumber = goToDialog.getLineNumber() - 1;
                
                if (lineNumber &gt;= 0 &amp;&amp; lineNumber &lt;= textArea.getLineCount()) {
                    try {
                        textArea.setCaretPosition(textArea.getLineStartOffset(lineNumber));
                    } catch (BadLocationException e) {
                        // should not occur since we already 
                        // checked if the lineNumber is in range.
                        e.printStackTrace();
                    }
                }
            }
        }
    });
}
</code></pre>
    <p>First we create an instance of our custom dialog passing in a reference to the main application frame and our main application. Next we call the method showDialog() on our dialog. This is a custom method that will be explained when we look at the dialog code. From this side this method will return false if the user cancels or closes the dialog. It will return true if they enter a line number and click the Go To button.</p>
    <p>If the user does click the Go To button then we will grab the line number that they entered. The JTextArea widget can track line numbers in the document. We will just check to make sure that the line number entered by the user is in the range of the current document and if it is then move to the line requested. The call to getLineStartOffset() throws a checked exception that we have to catch. We should not encounter this exception since we already checked that the number we are using is in range before using it.</p>
    <p>Next let’s look at the go to dialog. </p>
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure4-1.png" width="100%" alt="Figure 4-9: " title="Figure 4-9: " />
                <figcaption><strong>Figure 4-9:</strong> </figcaption>
            </figure>
        </div>
    </div>
<pre><code class="java">private JTextField lineNumberText;
private boolean performGoto = false;
</code></pre>
    <p>There are some similarities to the search dialog but we'll look at the whole thing anyway. First we’ll need a couple of class level fields.</p>
<pre><code class="java">public GoToDialog(JFrame frame, JNotepad jNotepad) {
    super(frame, "Go To Line", true);
    setLayout(new BorderLayout());
    JPanel mainPanel = new JPanel(new BorderLayout());
    add(mainPanel, BorderLayout.CENTER);
    mainPanel.setBorder(BorderFactory.createEmptyBorder(6, 5, 6, 5));
</code></pre>
    <p>Next the constructor. Pretty normal here. Call the super constructor with the parent frame, title and modality. Set the layout of the dialog to BorderLayout and create and add a mainPanel to the center. The mainPanel also has a BorderLayout and has some padding around the outside border.</p>
<pre><code class="java">JPanel labelPanel = new JPanel(new FlowLayout(FlowLayout.LEFT));
mainPanel.add(labelPanel, BorderLayout.NORTH);
JLabel lineNumberLabel = new JLabel("Line number:");
labelPanel.add(lineNumberLabel);
</code></pre>
    <p>Add a label to the north on the mainPanel and use a sub panel to flow it on the left side.</p>
<pre><code class="java">JPanel inputPanel = new JPanel(new FlowLayout(FlowLayout.LEFT));
mainPanel.add(inputPanel, BorderLayout.CENTER);
lineNumberText = new JTextField("1", 28);
addEscapeToActionMap(lineNumberText);
inputPanel.add(lineNumberText);
lineNumberLabel.setLabelFor(lineNumberText);
lineNumberLabel.setDisplayedMnemonic(KeyEvent.VK_L);
((PlainDocument)lineNumberText.getDocument()).setDocumentFilter(new NumberDocumentFilter());
lineNumberText.setSelectionStart(0);
lineNumberText.setSelectionEnd(1);
</code></pre>
    <p>Set up an inputPanel with FlowLayout and add it to the center of the mainPanel. Then add a text field to the inputPanel. The reason to use a FlowLayout in here is to prevent the BorderLayout from exploding the size of the text field. Even with pack() called a BorderLayout can make the text field slightly bigger than it should be and it can look bad. Next associate lineNumberLabel with the text field and give it a mnemonic. The last thing in this section is to set a filter on the document model for the text field. This is a filter of our own creation so I will show the code soon. All this filter does is only allow numbers to be entered in the text field.</p>
<pre><code class="java">JPanel bottomPanel = new JPanel(new BorderLayout());
mainPanel.add(bottomPanel, BorderLayout.SOUTH);
bottomPanel.setBorder(BorderFactory.createEmptyBorder(4, 0, 4, 0));
JPanel buttonPanel = new JPanel(new GridLayout(1, 0, 5, 5));
bottomPanel.add(buttonPanel, BorderLayout.EAST);
JButton goToButton = new JButton("Go To");
addEscapeToActionMap(goToButton);
buttonPanel.add(goToButton);
getRootPane().setDefaultButton(goToButton);
goToButton.setMnemonic(KeyEvent.VK_G);
JButton cancelButton = new JButton("Cancel");
addEscapeToActionMap(cancelButton);
buttonPanel.add(cancelButton);
</code></pre>
    <p>The last thing is to set a bottomPanel in the south of the mainPanel and then a buttonPanel in the east if the bottomPanel. The buttonPanel is set to a GridLayout with some spacing. This will place the buttons in the bottom right side of the dialog with some padding between them. It will also make both buttons the same size. We also add the letter ‘G’ to the mnemonic for the Go To button. The Cancel button doesn’t need a mnemonic since we are using the ESC key for this.</p>
<pre><code class="java">goToButton.addActionListener(new ActionListener() {
    public void actionPerformed(ActionEvent e) {
        performGoto = true;
        setVisible(false);
    }
});

cancelButton.addActionListener(new ActionListener() {
    public void actionPerformed(ActionEvent e) {
        performGoto = false;
        setVisible(false);
    }
});
</code></pre>
    <p>Next we add ActionListeners to the buttons. We simply set a boolean to true or false depending on whether Go To or Cancel was clicked. Then we hide the dialog. Since this is a modal dialog hiding it will allow execution to continue after the line that showed the dialog.</p>
<pre><code class="java">addWindowListener(new WindowAdapter() {
    public void windowDeactivated(WindowEvent e) {
        performGoto = false;
        dispose();
    }
});
</code></pre>
    <p>Next we want to listen for if the user clicks the ‘X’ to close the dialog instead of one of our buttons. This allows us to make sure the performGoto is set to false and the dialog is properly disposed of.</p>
<pre><code class="java">public boolean showDialog() {
    pack();
    centerDialog();
    setVisible(true);
    dispose();
    
    return performGoto;
}
</code></pre>
    <p>This method is called from JNotepad when the user clicks the Edit -> Go To menu item. We want control of this since it is a modal dialog. So we call pack() and centerDialog() to get it looking just right. Then we call setVisible(true). This method will block on modal dialogs until the dialog is hidden. It will just wait here until one of our buttons is clicked. Once that happens the dialog is hidden and execution can continue. Next we dispose of our dialog properly and return the boolean value for whichever button was clicked by the user.</p>
<pre><code class="java">public int getLineNumber() {
    return Integer.valueOf(lineNumberText.getText().isEmpty() ? 
            "1" : 
                lineNumberText.getText());
}
</code></pre>
    <p>Lastly we set up a method for us to get the line number that was entered. Since we have a filter on the text field we know that only integer values or an empty string can be present in the field.</p>
<pre><code class="java">public class NumberDocumentFilter extends DocumentFilter {
    private static final Pattern NUMBER_PATTERN = Pattern.compile("^\\d+$");
    
    public void insertString(FilterBypass fb, int offset, 
            String string, AttributeSet attr) throws BadLocationException {
        if (containsOnlyNumeric(string)) {
            fb.insertString(offset, string, attr);
        }
    }

    public void replace(FilterBypass fb, int offset, int length, 
            String text, AttributeSet attrs) throws BadLocationException {
        if (containsOnlyNumeric(text)) {
            fb.replace(offset, length, text, attrs);
        }
    }

    private boolean containsOnlyNumeric(String text) {
        return NUMBER_PATTERN.matcher(text).find();
    }
}
</code></pre>
    <p>This is our filter for the text field. First we set up a regular expression that will only accept numbers. Next we override both insertString(FilterBypass fb, int offset, String string, AttributeSet attr) and replace(FilterBypass fb, int offset, String string, AttributeSet attr) methods from javax.swing.text.DocumentFilter. One of these methods on the filter is called every time a character typed into a text field. Since we replaced the filter with our own and overrode those two methods we can control the allowed characters. All we do is check if it is a numberic value and continue with the insert if it is, otherwise reject it. Notice to allow the character into the document we use the javax.swing.text.DocumentFilter.FilterBypass class. If we didn’t use this class and tried to insert into the document directly it would go through the filter again in an infinite loop.</p>
<pre><code class="java">public void selectAll() {
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            textArea.setSelectionStart(0);        
            textArea.setSelectionEnd(textArea.getText().length());
        }
    });
}
</code></pre>
    <p>For Edit -> Select All there is not much to say. Selection is supported by JTextArea so you just have to tell it where to start and end the selection and it will highlight it for you.</p>
<pre><code class="java">private static final DateFormat DATE_FORMAT = new SimpleDateFormat("hh:mm aa yyyy-MM-dd");

public void timeDate() {
    SwingUtilities.invokeLater(new Runnable() {
        @Override
        public void run() {
            String timeDateString = DATE_FORMAT.format(new Date());
            int start = textArea.getSelectionStart();
            textArea.replaceSelection(timeDateString);
            textArea.setCaretPosition(start + timeDateString.length());
        }
    });
}
</code></pre>
    <p>There is not much more to Edit -> Time/Date. Just set up a DateFormat instance to perform the formatting as needed. The formatting is not going to change from use to use so we can just make it a constant. First we store the current date and time in a string. Then we see where the cursor is. We call replaceSelection(timeDateString) with our date string. If no text is selected then replaceSelection(timeDateString) will just insert the text at the current cursor position. Once the date string is inserted we move the cursor to the end of the insertion point.</p>
</div>
<div class="panel-footer foot-nav">
    <div class="row">
        <div class="col-md-4 col-sm-1"><a href="/startingswing/page.php?page=chap3">Showing a Window</a></div>
        <div class="col-md-4 col-sm-1 text-center"><a href="/startingswing/page.php?page=contents">Contents</a></div>
        <div class="col-md-4 col-sm-1"><span class="pull-right"><a href="/startingswing/page.php?page=chap5">Format Menu</a></span></div>
    </div>
</div>
