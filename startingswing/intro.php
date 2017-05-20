<div class="panel-heading">
    <h2 class="panel-title chapter-heading">Introduction</h2>
</div>
<div class="panel-body scroll">
    <p>Swing is Java's cross platform library for displaying GUIs for the user. In this book I hope to help you gain a basic understanding of how to build a GUI application with Swing. “The Java Tutorial” is great but it helps to go beyond that and build a full working application.</p>
    <p>Before Swing, Java came with the Abstract Windowing Toolkit (AWT) to build GUI applications. With AWT all widgets were tied directly to the underlying OS for drawing and functionality. Java just passed on events to you so you could interact with the user. Later Swing was introduced as a replacement for AWT. Swing is much more flexible than AWT in part due to the Pluggable Look and Feel (PLAF) system. This allows you to more easily change the appearance of your application by just switching to different look and feels or themes. The main drawback of Swing is performance. Since all drawing occurs in Java code, drawing can be slower than AWT. But with the power of machines these days that is hardly a problem. Most performance problems come from incorrect coding by the Swing application programmer.</p>
    <p>Some other terms you might hear referred to is light weight components and heavy weight components. AWT components are heavy weight components because they are drawn by the OS. Also because if you try to mix lightweight and heavyweight components in the same application without knowing what you are doing there can be problems. Heavyweight components will always draw on top of lightweight components.</p>
    <p>But what application should you build? Coming up with an idea is sometimes harder than the actual build. Especially if you haven't built a GUI application before. To make things easier just pick something that already exists and replicate it completely, as closely as you can.</p>
    <p>For this book I will be creating JNotepad, which will be a clone of Windows Notepad. I know Notepad is a pretty crappy application but you have to start somewhere. Besides once we get it built we can extend it with some of our own ideas.</p>
    <p>There is no one way to make an application. So in this book I will present how I do it and hope that it will be helpful to you. So let's begin.</p>
</div>
<div class="panel-footer foot-nav">
    <div class="row">
        <div class="col-md-4 col-sm-1"></div>
        <div class="col-md-4 col-sm-1 text-center"><a href="/startingswing/contents.php">Contents</a></div>
        <div class="col-md-4 col-sm-1"><span class="pull-right"><a href="/startingswing/page.php?page=chap1">Showing a Window</a></span></div>
    </div>
</div>
