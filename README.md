## 0x01. What is this project?

This project is a restructured version of a virtual ICP filing program. While retaining the previous functions, it has updated more features, such as support for plugins and themes.

## 0x02. Acknowledgements

WordPress - We have studied the WordPress plugin system and incorporated it into this project with minor modifications.

Layui - We have built the backend styling using Layui.

TuanICP - [GitHub](https://github.com/yuntuanzi/TuanICP) This project is the cornerstone. I was inspired by it, but I was not satisfied with its current functionality and coding style, so I decided to refactor it.

## 0x03. Note

Firstly, this project itself was developed for paid use. To this end, there is a function called `icp_auth()` that is responsible for detecting the authorization status. When you install and activate the `plus_pack` plugin, this function will work. It will obtain authorization information from your authorization platform based on `myauth`. For this purpose, you need to modify some variables and the authorization URL in the project. This function is located in the shared library folder: `super/system_a/lib/share_lib/lib.php`. The place where this function is used is in:` data/plugins/plus_pack/main.php`.

--- 

Secondly, this project is not perfect, and in fact, some features are still under development. Due to certain reasons, the maintenance may be very infrequent or even come to a complete halt. For this reason, I have decided to open-source this project. Additionally, the AB update framework used in this project has already been updated. This framework, which I developed independently, was inspired by the A/B update model of the Android system. Although the framework is not yet perfect, I will also open-source the new version of the framework soon.

---

And... This:

![0e8fd55d1b5f430a401711d592ad7e3](https://github.com/user-attachments/assets/53b671d2-1a0a-4d95-b168-173cbd6637aa)

The issue I raised is: [https://github.com/yuntuanzi/TuanICP/issues/3](https://github.com/yuntuanzi/TuanICP/issues/3)
