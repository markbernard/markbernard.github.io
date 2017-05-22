<div class="panel-heading">
    <h2 class="panel-title chapter-heading">File Menu</h2>
</div>
<div class="panel-body scroll">
    <p>In this chapter we'll look at all the File Menu options. Here they are.</p> 
    <div class="row">
        <div class="col-md-3">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure3-1.png" width="100%" alt="Figure 1-1: Window with no sizes applied" title="Figure 1-1: Window with no sizes applied" />
                <figcaption><strong>Figure 3-1:</strong> File menu options</figcaption>
            </figure>
        </div>
    </div>
    <p>There are seven options I'll cover here. New, Open, Save, Save As, Page Setup, Print and Exit. Notice that the Open menu, among others, has a ... after it. This is a conventions to tell the user that picking this item will open some sort of dialog window. It’s a good convention to follow since giving users hints about what can happen is important. Since we are going to start loading and saving text files, we will want to track if changes are made so we can warn the user if they are going to lose those changes.</p> 
<pre><code class="java">private static final String NEW_FILE_NAME = "Untitled";
</code></pre>
    <p>First there is a constant to show if a file has not been saved yet.</p>
<pre><code class="java">private boolean dirty;
private String fileName;
</code></pre>
    <p>Second here’s a variable to start tracking if changes have been made as well as one to keep the file name.</p>
<pre><code class="java">public JNotepad(JFrame parentFrame) {
    this.parentFrame = parentFrame;
    fileName = NEW_FILE_NAME;
    ApplicationPreferences.loadPrefs(parentFrame);
    dirty = false;
    setTitle();
    parentFrame.addWindowListener(this);
    setLayout(new BorderLayout());
    textArea = new JTextArea();
    textArea.setLineWrap(true);
    textArea.addKeyListener(this);
    JScrollPane scroll = new JScrollPane(textArea);
    add(scroll, BorderLayout.CENTER);
    createMenus();
}
</code></pre>
    <p>Next here is the latest version of the constructor. Since we are just starting up we will first set the fileName variable to the constant value defined earlier to mean that no file name has been chosen yet. Next we want to set the dirty flag to false since the application is just starting up. Then you can see a method call to <span class="inline-code">setTitle()</span>. That will be discussed after the load menu is seen. Next we want to listen to keyboard entries in the text area with <span class="inline-code">textArea.addKeyListener(this)</span>. This will allow us to adjust the dirty flag accordingly. Finally the last new thing is a call to the <span class="inline-code">createMenus()</span> method. This is what we are going to look at next.</p>
<pre><code class="java">private void createMenus() {
    JMenuBar bar = new JMenuBar();
    parentFrame.setJMenuBar(bar);

    JMenu fileMenu = new JMenu(new FileAction());
    bar.add(fileMenu);
    JMenuItem fileNewItem = new JMenuItem(new FileAction.NewAction(this));
    fileMenu.add(fileNewItem);
    JMenuItem fileOpenItem = new JMenuItem(new FileAction.OpenAction(this));
    fileMenu.add(fileOpenItem);
    JMenuItem fileSaveItem = new JMenuItem(new FileAction.SaveAction(this));
    fileMenu.add(fileSaveItem);
    JMenuItem fileSaveAsItem = new JMenuItem(new FileAction.SaveAsAction(this));
    fileMenu.add(fileSaveAsItem);
    fileMenu.addSeparator();
    JMenuItem filePageSetupItem = new JMenuItem(new FileAction.PageSetupAction());
    fileMenu.add(filePageSetupItem);
    JMenuItem filePrintItem = new JMenuItem(new FileAction.PrintAction());
    fileMenu.add(filePrintItem);
    fileMenu.addSeparator();
    JMenuItem fileExitItem = new JMenuItem(new FileAction.ExitAction(this));
    fileMenu.add(fileExitItem);
}
</code></pre>
    <p>There are 3 important classes used to create menus. They are <span class="inline-code">javax.swing.JMenuBar</span>, <span class="inline-code">javax.swing.JMenu</span> and <span class="inline-code">javax.swing.JMenuItem</span>. All applications can have a single menu bar. You need to create an instance of <span class="inline-code">JMenuBar</span> and set it to the <span class="inline-code">JFrame</span> for it to become visible. The <span class="inline-code">JMenuBar</span> can only contain <span class="inline-code">JMenu</span> instances. These will represent the main menu options that the user will see. For each <span class="inline-code">JMenu</span> instance you can add <span class="inline-code">JMenuItem</span> instances or <span class="inline-code">JMenu</span> instances. If you use <span class="inline-code">JMenu</span> this will create sub menus. If you use <span class="inline-code">JMenuItem</span> this represents a selectable item for the user. Selecting a <span class="inline-code">JMenuItem</span> will kick off an event that your application can capture.</p>
    <p>I mentioned events earlier when talking about the <span class="inline-code">WindowListener</span> interface. Events are the main driver of GUI based applications. They tell your program what the user is doing or what other things may be happening. The main event that menus create are called action events. There are different ways to handle action events. One way is to have a class that implements the <span class="inline-code">java.awt.event.ActionListener</span> interface. Then call <span class="inline-code">addActionListener(this)</span> on a menu class, passing in an instance of your class to it. A second way is to create an anonymous <span class="inline-code">ActionListener</span> class in the call to <span class="inline-code">addActionListener()</span>. A third way is to use the <span class="inline-code">javax.swing.Action</span> interface. I find the first 2 ways lead to a lot of messy code if you over use it. As your application gets bigger a lot more event handlers are going to be created and the code will get larger and larger. I like to use the <span class="inline-code">Action</span> interface via the <span class="inline-code">javax.swing.AbstractAction</span> class for most of my action handling code. This abstract class takes care of a lot of boiler plate code for you so you can concentrate on only writing code necessary to respond to user events. Also using that <span class="inline-code">Action</span> interface allows you to more easily reuse code. For example if you add a toolbar to your application and you put a save button on the toolbar you can use the same class to set up the action for both the File > Save menu option and the save toolbar button. I have a chapter for creating a toolbar once all the standard features are complete.</p> 
    <p>In this case I created a class called <span class="inline-code">FileAction</span> that extends <span class="inline-code">AbstractAction</span>.</p>
<pre><code class="java">public class FileAction extends AbstractAction {
    private static final long serialVersionUID = -7881302815437732429L;

    public FileAction() {
        putValue(Action.MNEMONIC_KEY, KeyEvent.VK_F);
        putValue(Action.NAME, "File");
    }

    public void actionPerformed(ActionEvent arg0) {
    }
}
</code></pre>
    <p>There’s not much to it. The contract for the <span class="inline-code">AbstractAction</span> class requires that you implement a method called <span class="inline-code">public void actionPerformed(ActionEvent arg0)</span>. This will be called when an action event is issued. In the case of the file menu no actions will be issued since the only job for a <span class="inline-code">JMenu</span> is to hold and show <span class="inline-code">JMenuItem</span>s or sub menus.</p>
    <p>In the constructor we want to set up how the action will behave. We can manipulate the behaviour with a number of key value pairs. The full list can be found in the JavaDoc for the <span class="inline-code">Action</span> interface. For our class we just need two. The <span class="inline-code">MNEMONIC_KEY</span> defines which key will activate the menu when combined with the ALT key. So for the file menu we will press ALT-F to expand the menu. The <span class="inline-code">NAME</span> defines what text will be displayed to the user on the menu.</p>
    <p>For the rest of the menu items in the file menu I created a static class for each one inside the <span class="inline-code">FileAction</span> class. This allows me to keep all the code in one place for each set of menus. The next action responds to the use selecting New from the File menu.</p>
<pre><code class="java">public static class NewAction extends AbstractAction {
    private static final long serialVersionUID = 8935736532927739892L;
    private JNotepad jNotepad;

    public NewAction(JNotepad jNotepad) {
        this.jNotepad = jNotepad;
        putValue(Action.MNEMONIC_KEY, KeyEvent.VK_N);
        putValue(Action.ACCELERATOR_KEY, 
                KeyStroke.getKeyStroke(KeyEvent.VK_N, InputEvent.CTRL_DOWN_MASK));
        putValue(Action.NAME, "New");
        putValue(Action.SHORT_DESCRIPTION, "Create a new blank document.");
    }

    public void actionPerformed(ActionEvent e) {
        new Thread(new Runnable() {
            public void run() {
                jNotepad.newDocument();
            }
        }, "New Document").start();
    }
}
</code></pre>
    <p>The constructor has three of new lines. The first one is just to keep an instance of our application handy so we can make it do what the user is asking for. The second one is setting a value to the key <span class="inline-code">ACCELERATOR_KEY</span>. This setting allows the user to activate this action by pressing a combination of keys instead of opening the menu. In this case CTRL-N will create a new document the same as selecting File > New from the menus. It will also show the accelerator key beside the menu option to remind the user that it is there. The third is using <span class="inline-code">SHORT_DESCRIPTION</span>. This allows you to set a tooltip. If the user hovers over the menu selection thinking about what to do it will give a hint using this description.</p>
    <p>The next thing to notice is that in the <span class="inline-code">actionPerformed(ActionEvent e)</span> method we are creating a new thread and starting it. It may seem like overkill to launch a new thread just to do something as simple as creating a new empty document but there is good reason. If our only concern was creating the new document then a new thread would be overkill. When dealing with events more care is required. Events are always processed via the Event Dispatch Thread (EDT). Any code that runs inside an event handler, in this case the <span class="inline-code">actionPerformed(ActionEvent e)</span> method, is executed on the EDT. While code is executing in the EDT your application becomes unresponsive to the user since only one event can be handled at a time. You might think to yourself that creating a new document doesn’t take much time and you can just run it directly. Most of the time you are probably right, but you should use this pattern to program defensively. It does not hurt your application to launch new threads, especially since most of them will be short lived. And if your code unexpectedly runs for an extended period of time you won’t tie up the EDT.</p>
    <p>Some people may say, “Why don’t you use <span class="inline-code">javax.swing.SwingUtilities.invokeLater(Runnable)</span>?” This is one way to do it but you cannot then name your threads. In the example above the thread is named “New Document”. This can be helpful during debugging or if a thread dump is performed. You can see all the active threads that are yours because you named them.</p>
    <p>Once the thread starts it calls <span class="inline-code">jNotepad.newDocument()</span>. We can look at this code next.</p>
<pre><code class="java">public void newDocument() {
    DirtyStatus status = isDirty();

    boolean saveCompleted = true;
    if (status.equals(DirtyStatus.SAVE_FILE)) {
        saveCompleted = save();
    } else if (status.equals(DirtyStatus.CANCEL_ACTION)) {
        saveCompleted = false;
    }

    if (saveCompleted) {
        ApplicationPreferences.setCurrentFileName(NEW_FILE_NAME);
        textArea.setText("");
        dirty = false;
        setTitle();
    }
}

private DirtyStatus isDirty() {
    DirtyStatus result = DirtyStatus.DONT_SAVE_FILE;

    if (dirty) {
        String filePath = (fileName.equals(NEW_FILE_NAME) ? fileName : 
            ApplicationPreferences.getCurrentFilePath() + FILE_SEPARATOR + fileName);
        int choice = JOptionPane.showOptionDialog(this, 
                "Do you want to save changes to " + filePath + "?", 
                "JNotepad", 
                JOptionPane.YES_NO_CANCEL_OPTION, 
                JOptionPane.PLAIN_MESSAGE,
                null,
                new String[] {"Save", "Don't Save", "Cancel"}, 
                "Save");
        if (choice == JOptionPane.YES_OPTION) {
            result = DirtyStatus.SAVE_FILE;
        } else if (choice == JOptionPane.NO_OPTION) {
            result = DirtyStatus.DONT_SAVE_FILE;
        } else if (choice == JOptionPane.CANCEL_OPTION) {
            result = DirtyStatus.CANCEL_ACTION;
        }
    }

    return result;
}

enum DirtyStatus {
    SAVE_FILE, DONT_SAVE_FILE, CANCEL_ACTION;
}
</code></pre>
    <p>Let’s look at <span class="inline-code">DirtyStatus</span> first. Typically when you check with the user for what they want to do if the current document is not saved and changes are going to be lost you give them three options. They can save the file, not save the file or stop the current action and continue as before. The <span class="inline-code">DirtyStatus</span> enum represents what the user has responded. In any call to <span class="inline-code">isDirty()</span> we want to ask the user what to do, if the document is dirty, and return the appropriate result. This method is set up so that it can be called from multiple different areas in the application, not just when creating a new document. It needs to stay generic enough for that purpose. So we just return what the user would reply to the dialog and each individual area will handle the result. First we set the default value to <span class="inline-code">DONT_SAVE_FILE</span>. If the document is not dirty then we do not want to overwrite the file with what is already there. <span class="inline-code">DONT_SAVE_FILE</span> tells the application to continue with any action without performing any kind of save. Next the dirty flag is checked and if it is set then show the user the dialog. Based on the user’s response we will set the result appropriately.</p>
    <p>The class <span class="inline-code">javax.swing.JOptionPane</span> is used to show simple basic dialogs to the user. If you have a simple informational message or a question with limited options then this is the way to go. Later on you will also see some custom more complicated dialogs that require custom classes.</p>
    <p>In this case I am using <span class="inline-code">showOptionDialog(Component parentComponent, Object message, String title, int optionType, int messageType, Icon icon, Object[] options, Object initialValue)</span>. Any component can be passed as the <span class="inline-code">parentComponent</span>. This just helps the dialog pop up over the provided component. The second argument is the message you want display to the user. If you pass in a string then that text will be displayed in the dialog. If you pass in a Swing component, that component will be displayed. This allows you to dress up option pane type dialogs. The third is the title that will be in the title bar. The fourth parameter is what options will be shown to the user. In this case I want a Save button, Don’t Save button and Cancel button. The fifth parameter is for the type of message, such as error, question, warning or plain. The type of message will also determine the type of icon to be displayed. For this message it is a plain. The next parameter is an Icon. You can place a custom icon if you wish. If you pass in null then the default icon for the message type will be used. Since we are using the plain message then there will be no icon. The options parameter requires an array of objects. If you pass in components for the options then they will be displayed as components. If you pass in any non-string argument the <span class="inline-code">toString()</span> will be called on each object and displayed as text in a button. In our case we are passing in an array of three strings. Since the number of strings that we are using matches the option type we can use the same constants to check the results. The last argument will highlight the selected value as long as it matches one of the values passed in. If it is set to null then no button will be highlighted. The next image (figure 3-2) shows what the dialog will look like.</p>
    <div class="row">
        <div class="col-md-6">
            <figure>
                <img class="img-responsive" src="/startingswing/res/figure3-2.png" width="100%" alt="Figure 3-2: Window with no sizes applied" title="Figure 1-1: Window with no sizes applied" />
                <figcaption><strong>Figure 3-2:</strong> DIalog to save changes</figcaption>
            </figure>
        </div>
    </div>
    <p>Now we can look at the rest of the <span class="inline-code">newDocument()</span> method. First we’ll create a boolean called <span class="inline-code">saveCompleted</span>. We’ll use this to keep track of what the user decides. We’ll set it to false initially. If the document is not dirty this will allow us to just continue on since no user action is required.</p>
    <p>Next if the document is dirty and the user wants to save the file we’ll call the <span class="inline-code">save()</span> method. This will return true if the document was saved successfully. It will return false for any sort of problem that prevents saving. We’ll assign the result of save to the <span class="inline-code">saveCompleted</span> variable. We’ll look at the <span class="inline-code">save()</span> method when we look at the File > Save menu item.</p>
    <p>If the user clicked cancel on the dialog then we don’t want to go on so we assign false to <span class="inline-code">saveCompleted</span>. If the document was not dirty or the user clicked No on the dialog then DONT_SAVE_FILE is the status and we don’t need an “if” statement for that since, in this case, we want the default action which is already having true assigned to saveCompleted.</p>
    <p>To recap <span class="inline-code">saveCompleted</span> will be set to true if the document is not dirty, the user clicks no on the dialog or the user clicks yes and the document is successfully saved. If that is the case then we will proceed with creating a new document. There are 3 lines for this. The first line will change the file name on the document back to “Untitled” as defined by the constant <span class="inline-code">NEW_FILE_NAME</span>. The next line will clear all text from the <span class="inline-code">JTextArea</span>. The third line will call <span class="inline-code">setTitle()</span> to update the title bar to give the user a hint of what is going on. Here is what <span class="inline-code">setTitle()</span> looks like.</p>
<pre><code class="java">private void setTitle() {
    parentFrame.setTitle((dirty ? "*" : "") + fileName + " - " + APPLICATION_TITLE);
}
</code></pre>
    <p>It’s a pretty simple one liner but there are 3 distinct parts. At the end is the constant <span class="inline-code">APPLICATION_TITLE</span> which is just the name of our application, JNotepad. You always want to make sure the user knows they are in your application so put it in there. In the middle is a call to get the file name of the current file that the user is working on. If they just started up or asked for a new document this will be “Untitled”, otherwise it will be the file name without any path information. At the front we check to see if the document is dirty and give an indication to the user if it is by showing an asterisk in the title bar.</p>
    <p>After the New menu is the Open menu. We also use a static class inside <span class="inline-code">FileAction</span> for taking care of File > Open.</p>
<pre><code class="java">public static class OpenAction extends AbstractAction {
    private static final long serialVersionUID = 3954227951651230619L;
    private JNotepad jNotepad;

    public OpenAction(JNotepad jNotepad) {
        this.jNotepad = jNotepad;
        putValue(Action.MNEMONIC_KEY, KeyEvent.VK_O);
        putValue(Action.ACCELERATOR_KEY, 
                KeyStroke.getKeyStroke(KeyEvent.VK_O, InputEvent.CTRL_DOWN_MASK));
        putValue(Action.NAME, "Open...");
        putValue(Action.SHORT_DESCRIPTION, "Load a document from disk.");
    }

    public void actionPerformed(ActionEvent e) {
        new Thread(new Runnable() {
            public void run() {
                jNotepad.load();
            }
        }, "Open").start();
    }
}
</code></pre>
    <p>This class is pretty much the same as the last one. In the constructor store a reference to the application and set the mnemonic, accelerator, name and tooltip. Then, in the event handler, call <span class="inline-code">JNotePad.load()</span> to perform the operation.</p>
<pre><code class="java">public void load() {
    DirtyStatus result = isDirty();
    
    boolean saveSuccessful = true;
    if (result.equals(DirtyStatus.SAVE_FILE)) {
        saveSuccessful = save();
    } else if (result.equals(DirtyStatus.CANCEL_ACTION)) {
        saveSuccessful = false;
    }
    
    if (saveSuccessful) {
        String filePath = ApplicationPreferences.getCurrentFilePath();
        JFileChooser fileChooser = new JFileChooser(filePath);
        if (fileChooser.showOpenDialog(this) == JFileChooser.APPROVE_OPTION) {
            File selectedFile = fileChooser.getSelectedFile();
            fileName = selectedFile.getName();
            ApplicationPreferences.setCurrentFilePath(
                    selectedFile.getParentFile().getAbsolutePath().replace("\\", "/"));
            loadFile(selectedFile);
        }
    }
}
</code></pre>
    <p>In the load method first check to see if the document is dirty. Then, once that is resolved, get the last path that the user had used. Then create an instance of <span class="inline-code">javax.swing.JFileChooser</span> passing in the last user path. The <span class="inline-code">JFileChooser</span> class is a built in dialog that allows the user to browse the file system to select one or more files or directories. If you look at the API for <span class="inline-code">JFileChooser</span> you will see various options that will let you determine whether you want to let the user select a file or a directory, allow for multiple file selection or just single and put a filter on file types to display.</p>
    <p>Next we call <span class="inline-code">showOpenDialog()</span>. This method takes a component as an argument so it will know where to display the dialog. We pass in a handle to our application since it is also a component. <span class="inline-code">showOpenDialog()</span> is also a blocking operation. It will not return until the user has clicked an option on the dialog. The user will see an Open button and a Cancel button. There are constants defined in the <span class="inline-code">JFileChooser</span> class that will allow you to check what the result was. They are <span class="inline-code">APPROVE_OPTION</span> and <span class="inline-code">CANCEL_OPTION</span>. If the user clicks the “X” to close the dialog window that is the same as clicking cancel.</p>
    <p>If the user clicks cancel then nothing else will happen. If the user clicks open then we can get the file they selected by calling <span class="inline-code">getSelectedFile()</span> on the <span class="inline-code">JFileChooser</span> instance. We now break the file up into its name and path so we can store it for later use. The name will be used to display in the title bar and the path will be used for future file dialogs. It will also be saved when the application exits.</p>
    <p>The actions for Save, Save As, Page Setup, Print and Exit are pretty much identical to the others. Do the setup in the constructor and then in the event handler call the appropriate method on the JNotePad application. The methods we haven’t seen yet are <span class="inline-code">save()</span>, <span class="inline-code">saveAs()</span>, <span class="inline-code">pageSetup()</span>, <span class="inline-code">doPrint()</span> and <span class="inline-code">exit()</span>. I’ll go over each one next.</p>
<pre><code class="java">public boolean save() {
    if (fileName.equals(NEW_FILE_NAME)) {
        return saveAs();
    } else {
        saveFile(ApplicationPreferences.getCurrentFilePath() + "/" + fileName);
        dirty = false;
        setTitle();
        
        return true;
    }
}
</code></pre>
    <p>This is the <span class="inline-code">save()</span> method. The first thing we do is check if this document already has a name from a previous load/save operation or if it is a fresh document. If it is fresh the call <span class="inline-code">saveAs()</span> to display the dialog to get a location to save. If the document already has a file path then just save the contents to that file, by calling <span class="inline-code">saveFile(path)</span>. We’ll look at that method shortly. Once the save completes set dirty to false and update the title to reflect that the document has been saved.</p>
<pre><code class="java">public boolean saveAs() {
    boolean result = true;
    
    String filePath = ApplicationPreferences.getCurrentFilePath();
    JFileChooser fileChooser = new JFileChooser(filePath);
    if (fileChooser.showSaveDialog(this) == JFileChooser.APPROVE_OPTION) {
        File selectedFile = fileChooser.getSelectedFile();
        fileName = selectedFile.getName();
        ApplicationPreferences.setCurrentFilePath(
                selectedFile.getParentFile().getAbsolutePath().replace("\\", "/"));
        saveFile(selectedFile.getAbsolutePath());
        dirty = false;
        setTitle();
    } else {
        result = false;
    }
    
    return result;
}
</code></pre>
    <p>This method will use the now familiar <span class="inline-code">JFileChooser</span> dialog to ask the user to save the file. This time we call <span class="inline-code">showSaveDialog()</span> instead, since we are saving. <span class="inline-code">showSaveDialog()</span> and <span class="inline-code">showOpenDialog()</span> are just convenience methods that call showDialog(Component, String). The String argument is the text that will be displayed on the button that will return APPROVE_OPTION. Save and Open are the most commonly used so adding convenience methods is helpful to the programmer.</p>
    <p>If the user clicked Save we will store the name and path for later use and then call <span class="inline-code">saveFile()</span> with the path to the file. Once the save is complete set the dirty flag to false and update the title.</p>
<pre><code class="java">private void saveFile(String path) {
    Writer out = null;
    
    try {
        out = new OutputStreamWriter(new FileOutputStream(path), "UTF-8");
        out.write(textArea.getText());
    } catch (UnsupportedEncodingException e) {
        //UTF-8 is built into Java so this exception should never be thrown
    } catch (FileNotFoundException e) {
        JOptionPane.showMessageDialog(this, "Unable to create the file: " + path + "\n" + e.getMessage(), "Error loading file", JOptionPane.ERROR_MESSAGE);
    } catch (IOException e) {
        JOptionPane.showMessageDialog(this, "Unable to save the file: " + path, "Error loading file", JOptionPane.ERROR_MESSAGE);
    } finally {
        ResourceCleanup.close(out);
    }
}
</code></pre>
    <p>Both <span class="inline-code">save()</span> and <span class="inline-code">saveAs()</span> call <span class="inline-code">saveFile(String path)</span> to do the work of the actual save. The first two perform the calculations and user interactions to make sure a save is going to happen. Once it does happen then we finally perform the save.</p> 
    <p>You’ll notice the first line of code declares as <span class="inline-code">java.io.Writer</span> local variable outside of the try…catch block. The reason for this is we need to be able cleanup any open files whether or not any errors occur. If an error does occur the cleanup happens in the finally block so we need a way of having a reference to what is going on in the try block. I am always in the habit of coding to the highest level interface or class I can get away with. This always makes it easier to refactor later on if it is needed. It may sometimes seem like a waste of time but once you are used to coding this way it is just as fast as not doing it.</p>
    <p>Inside the try block there are only 2 lines so this is pretty simple. You can also see that we are using a <span class="inline-code">java.io.FileOutputStream</span> to write to a file. You might question though what the <span class="inline-code">java.io.OutputStreamWriter</span> is for. The <span class="inline-code">FileOutputStream</span> gives you direct access to writing to a file. But you can only write bytes to any kind of <span class="inline-code">java.io.OutputStream</span>. We want to write out character data to the file but handling that yourself can sometimes be tricky. So by wrapping the <span class="inline-code">FileOutputStream</span> in an <span class="inline-code">OutputStreamWriter</span> we can dictate more easily what kind of character data will be written. The second argument to the <span class="inline-code">OutputStreamWriter</span> constructor is a string representing the type of encoding that we want to use. In this case we will just write everything out as UTF-8 to keep things simple. UTF-8 is the most common type of format now so it will work in most cases.</p>
    <p>The <span class="inline-code">write(String str)</span> method of the Writer interface takes a String that it will write out to the stream. This makes it easy for us since our <span class="inline-code">JTextArea</span> has a method called <span class="inline-code">getText()</span> that will return a string of everything it contains. We can just pass this string directly to the <span class="inline-code">Writer</span>. If all goes well then the finally block will be executed next. </p>
<pre><code class="java">public static final void close(Writer writer) {
    if (writer != null) {
        try {
            writer.close();
        } catch (IOException e) {
            //cannot do anything
        }
    }
}
</code></pre>
    <p>I created the <span class="inline-code">ResourceCleanup</span> class to more cleanly handle the cleanup routine. If we look inside the <span class="inline-code">close(Writer writer)</span> method this is what you see. There is not a lot going on here but first we have to check for null. After that the close method of any stream or reader/writer declares that it can throw an <span class="inline-code">IOException</span> so we have to wrap it in a try…catch block. There is nothing you can do if a <span class="inline-code">close()</span> fails. That is why the catch block is empty. That is a lot of code that will make your main code area look messy. So you can replace it with a single line and hide the details in this little utility class.</p>
    <p>Back in the <span class="inline-code">saveFile()</span> method there are three catch blocks in case something goes wrong. Possible exceptions are <span class="inline-code">java.io.UnsupportedEncodingException</span>, <span class="inline-code">java.io.FileNotFoundException</span> and <span class="inline-code">java.io.IOException</span>. The first two are subclasses of the third. The order of exceptions are important. More specific exceptions must come before more general. If you were to try and place <span class="inline-code">IOException</span> first then you would get a compiler error when trying to put the other two next.</p>
    <p>If you are only dealing with UTF-8 encoding then you will never see an UnsupportedEncodingException. UTF-8 is built into the JVM so it is always present. All implementations of the Java platform must also support US-ASCII, ISO-8859-1, UTF-16, UTF16BE and UTF-16LE. If you try with these encodings there will never be an exception. However others may they not be present so it could happen. In the case of this example application you could allow the <span class="inline-code">IOException</span> handler take care of it. I just put it in for completeness.</p>
    <p>Normally if the way you handle two different exceptions is the same and one is a subclass of another then you could just declare the parent exception to handle both cases. In this case we want to give the user a slightly different message if a <span class="inline-code">FileNotFoundException</span> occurs. We can tell the user that the file could not be created. The exception will also have a message, such as “Permission denied”, that you can pass on to the user.</p>
    <p>If an <span class="inline-code">IOException</span> occurs then you can give the more general message to the user. You also don’t want to give the exception message since, with <span class="inline-code">IOException</span>, they are usually more cryptic. If one of these three exceptions occurs then you will still end up in the finally block. If this is the case then there is a good chance that the out variable will be null. This is why in the <span class="inline-code">ResourceCleanup</span> class we have to check for null. In either case we want to close the stream if it was opened, otherwise we could run out of resources by leaving file handles open.</p>
<pre><code class="java">private PrintRequestAttributeSet printRequestAttributeSet;
</code></pre>
    <p>Basic printing is a pretty easy task for use. You just need a variable of the class javax.print.attribute.PrintAttributeSet to hold the user's selections for printing. So declare the variable as a field.</p>
<pre><code class="java">public void pageSetup() {
    if (printRequestAttributeSet == null) {
        printRequestAttributeSet = new HashPrintRequestAttributeSet();
    }
    PrinterJob.getPrinterJob().pageDialog(printRequestAttributeSet);
}
</code></pre>
    <p>Java provides a class to access the print functionality called <span class="inline-code">java.awt.print.PrinterJob</span>. First check to see if we already have some user settings stored. If not then create an empty set that will be populated by the print dialog. The method <span class="inline-code">getPrinterJob()</span> is a static method that creates an instance of PrinterJob that will be tied to OS resources for the printer. Calling <span class="inline-code">pageDialog(printRequestAttributeSet)</span> will show a dialog to the user to change basic settings for margins and paper choice. By passing in our <span class="inline-code">PrintAttributeSet</span>, the user settings will be store there.</p>
<pre><code class="java">public void doPrint() {
    try {
        textArea.print(null, 
                new MessageFormat("page {0}"), 
                true, 
                PrinterJob.getPrinterJob().getPrintService(), 
                printRequestAttributeSet, 
                true);
    } catch (PrinterException e) {
        e.printStackTrace();
    }
}
</code></pre>
    <p>The <span class="inline-code">doPrint()</span> method is called when the user selects print from the file menu. For basic printing it is pretty simple. Just call the <span class="inline-code">print(MessageFormat headerFormat, MessageFormat footerFormat, boolean showPrintDialog, PrintService service, PrintRequestAttributeSet attributes, boolean interactive)</span> method on the <span class="inline-code">JTextArea</span> that contains our content. There are three different <span class="inline-code">print()</span> methods that can be used. We are going to use the one that has 6 parameters. The first two are used if you want a header and/or a footer. If you don't want them you can set them to null. We don't want a header so we will set it to null. For the footer we'll set it up to print the page number. Both these options use the <span class="inline-code">java.text.MessageFormat</span> class to encode the text. The full codes that you can use are in the Java Doc for this class. For our case we are using {0}, which tells the formatter to insert the first argument into the message string. For the footer this is the page number.</p>
    <p>Passing true for the third argument will display a print dialog to the user so they can changes settings and print or cancel. The fourth argument is the <span class="inline-code">javax.print.PrintService</span> and we can just get that from the <span class="inline-code">PrinterJob</span>. The fifth argument is the page settings that we have stored for the user. One tab of the dialog will allow the user to change these settings again if they wish. Passing true to the last argument will print in interactive mode. This will show a progress dialog and allow the user to cancel if they wish.</p>
<pre><code class="java">public void exit() {
    DirtyStatus status = isDirty();
    
    boolean saveCompleted = true;
    if (status.equals(DirtyStatus.SAVE_FILE)) {
        saveCompleted = save();
    } else if (status.equals(DirtyStatus.CANCEL_ACTION)) {
        saveCompleted = false;
    }

    if (saveCompleted) {
        ApplicationPreferences.savePrefs(parentFrame);
        System.exit(0);
    }
}
</code></pre>
    <p>The <span class="inline-code">exit()</span> method has now been expanded to check for the dirty flag. This works the same way as the <span class="inline-code">newDocument()</span> method. Ask the user what they want to do with the dirty document. If they want to save or don’t care then we set <span class="inline-code">saveCompleted</span> to true. If this is the case then we save the current settings for the applciation and exit. If the user decides they want to do something and clicks cancel, then <span class="inline-code">saveComplete</span> is set to false and we do not exit the application.</p>
</div>
<div class="panel-footer foot-nav">
    <div class="row">
        <div class="col-md-4 col-sm-1"><a href="/startingswing/page.php?page=chap2">Editing Area</a></div>
        <div class="col-md-4 col-sm-1 text-center"><a href="/startingswing/page.php?page=contents">Contents</a></div>
        <div class="col-md-4 col-sm-1"><span class="pull-right"><a href="/startingswing/page.php?page=chap4">Edit Menu</a></span></div>
    </div>
</div>
    
