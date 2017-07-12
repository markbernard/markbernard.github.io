<div class="panel-heading">
    <h2 class="panel-title chapter-heading">Editing Area</h2>
</div>
<div class="panel-body scroll">
	<p>Now let’s get something on the screen that we can use. The base class for almost all visible widgets in Swing is <span class="inline-code">javax.swing.JComponent</span>. To show something on the screen you need a container. The most common containers in Swing are <span class="inline-code">JFrame</span> and <span class="inline-code">JPanel</span>. There are also 2 special containers: <span class="inline-code">JFrame</span> and <span class="inline-code">JWindow</span>. These 2 are the only part of Swing that are not <span class="inline-code">JComponent</span> sub classes. This is important for 2 reasons. One is that other containers can be placed inside containers since they are also <span class="inline-code">JComponent</span>s. For example you can put a <span class="inline-code">JPanel</span> inside another <span class="inline-code">JPanel</span>, but you cannot put a <span class="inline-code">JFrame</span> inside a <span class="inline-code">JPanel</span> or another <span class="inline-code">JFrame</span>. The second is that they are both tied to the native platform window. This allows Java to easily draw its own stuff onto the native screen.</p>
	<p>To start we are going to have our main <span class="inline-code">JNotepad</span> class extend <span class="inline-code">JPanel</span>. I have always found it easier to have my application class extend <span class="inline-code">JPanel</span> over <span class="inline-code">JFrame</span> because it gives you better control over sizing your components when necessary. For example if you call <span class="inline-code">JFrame.setSize(800, 600)</span> then your window will be 800x600 but the useable area is smaller since the title bar, menu and frame all take up space. Here are the changes:</p>
<pre><code class="java">public class JNotepad extends JPanel implements WindowListener {
    private static final long serialVersionUID = -2119311360500754201L;
    private JFrame parentFrame;
    private JTextArea textArea;

    public JNotepad(JFrame parentFrame) {
        this.parentFrame = parentFrame;
        ApplicationPreferences.loadPrefs(parentFrame);
        parentFrame.addWindowListener(this);
        setLayout(new BorderLayout());
        textArea = new JTextArea();
        textArea.setLineWrap(true);
        JScrollPane scroll = new JScrollPane(textArea);
        add(scroll, BorderLayout.CENTER);
    }

    public static void main(String[] args) {
        try {
            // Make the application look native.
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
        } catch (Exception e) {
            // System look and feel is always present.
        }

        SwingUtilities.invokeLater(new Runnable() {
            @Override
            public void run() {
                JFrame frame = new JFrame("JNotepad");
                frame.setLayout(new BorderLayout());
                JNotepad jNotepad = new JNotepad(frame);
                frame.add(jNotepad, BorderLayout.CENTER);
                frame.setVisible(true);
            }
        });
    }
}
</code></pre>
	<p>Any time you extend a Swing class, your class becomes <span class="inline-code">Serializable</span>. <span class="inline-code">Serializable</span> classes should have a variable called <span class="inline-code">serialVersionUID</span>. It is not strictly necessary with this kind of application since we won't be using <span class="inline-code">Serializable</span> capabilities on any of our classes but if you don't add it you will get a warning that it is missing. I don't like to leave warnings in any of my programs so I always add it. And if you are using Eclipse you can get it generated for you or set it to 1.</p>
	<p>Next you'll notice <span class="inline-code">private JTextArea textArea</span>. As the names implies this is a widget that will allow you to enter lines of text. With Swing none of the widgets provide their own scrolling capability. This is provided by the <span class="inline-code">JScrollPane</span> class to afford greater flexibility in how to use it. To add your text area to a scroll pane just use the line <span class="inline-code">JScrollPane scroll = new JScrollPane(textArea)</span>. This will give both horizontal and vertical scrolling. You can also force it to only use horizontal or only use vertical. <span class="inline-code">textArea.setLineWrap(true)</span> will also affect scrollbars. Setting it to true will automatically wrap long lines. This prevents the horizontal scrollbar from showing up. For now we will set it to true. Later on there will be a menu option to control this. Finally add the scroll pane to our class, that extends <span class="inline-code">JPanel</span>, and we can now have a visible area to enter text.</p> 
	<p>Layout managers are an important part of Swing for most applications that use it. You can see that I use the <span class="inline-code">BorderLayout</span> class here. We’ll look at layout managers later on when building some dialogs.</p>
	<p>Now down in the main method where we start everything we want to create an instance of our class to add to the frame. This will now allow us to see it when the application starts and the frame is set to visible. A <span class="inline-code">JFrame</span> is never visible by default to allow you to have greater flexibility when creating them. For example, you may have more than one but want to create them all at the same time and show them at your convenience.</p>
</div>
<div class="panel-footer foot-nav">
    <div class="row">
        <div class="col-md-4 col-sm-1"><a href="/startingswing/page.php?page=chap1">Editing Area</a></div>
        <div class="col-md-4 col-sm-1 text-center"><a href="/startingswing/page.php?page=contents">Contents</a></div>
        <div class="col-md-4 col-sm-1"><span class="pull-right"><a href="/startingswing/page.php?page=chap3">File Menu</a></span></div>
    </div>
</div>
